<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\Actuality;
use App\Entity\CircuitTour;
use App\Entity\Team;
use App\Entity\Guide;
use App\Entity\Notification;
use App\Entity\Tournament;
use App\Entity\User;
use App\Entity\Vote;
use App\Repository\CommentRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\ErrorManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends AbstractController
{
    final public const PARENTS = ['actuality', 'guide', 'team', 'tournament', 'circuitTour'];

    public function __construct(private readonly CommentRepository $repo, private readonly EntityManagerInterface $em)
    {
    }

    private function getParentClass(string $entity): ?string
    {
        switch ($entity) {
            case 'team':
                return Team::class;
                break;
            case 'guide':
                return Guide::class;
                break;
            case 'actuality':
                return Actuality::class;
                break;
            case 'tournament':
                return Tournament::class;
                break;
            case 'circuitTour':
                return CircuitTour::class;
                break;
            default:
                return null;
        }
    }

    private function getParentRepo(string $entity): ?ServiceEntityRepository
    {
        $className = $this->getParentClass($entity);
        if (is_null($className)) return null;
        return $this->em->getRepository($className);
    }

    private function translateEntity(string $entity): ?string
    {
        switch ($entity) {
            case 'team':
                return 'Équipe';
            case 'guide':
                return 'Guide';
            case 'actuality':
                return 'Actualité';
            case 'tournament':
            case 'circuitTour':
                return 'Tournoi';
            default:
                return null;
        }
    }

    private function ellipsComment(Comment $comment): string
    {
        $content = $comment->getContent();
        return strlen($content) > 100
            ? mb_substr($content, 0, 97) . '...'
            : $content;
    }

    #[Route(path: '/comments/{entity}/user/{id}', name: 'comments_by_user', methods: ['GET'])]
    public function getCommentsByUser($entity, $id, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);
        if (empty($user)) {
            return new JsonResponse(
                ['message' => "Utilisateur introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        $comments = $this->repo->findByUser($user, $entity);
        if ($this->getUser()) {
            foreach ($comments as $comment) {
                $comment->setOwnUserVote($comment->getUserVote($this->getUser()));
            }
        }

        return $this->json(
            ['comments' => $comments],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:list']]
        );
    }

    #[Route(path: '/comments/{entity}/{id}', name: 'comments', methods: ['GET'])]
    public function getComments($entity, $id)
    {
        $parentRepo = $this->getParentRepo($entity);
        if (is_null($parentRepo)) {
            return new JsonResponse(
                ['message' => "Mauvaise entité."],
                Response::HTTP_NOT_FOUND
            );
        }

        $parent = $parentRepo->find($id);
        if (empty($parent)) {
            return new JsonResponse(
                ['message' => $this->translateEntity($entity) . " introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        /** @var User */
        $user = $this->getUser();
        $groups = ['read:list'];
        if ($user && $user->getIsModo()) {
            $groups[] = 'read:comment:admin';
        }

        $comments = $this->repo->findByParent($parent, $entity, $user);
        if ($user) {
            foreach ($comments as $comment) {
                $comment->setOwnUserVote($comment->getUserVote($user));
            }
        }

        return $this->json(
            ['comments' => $comments],
            Response::HTTP_OK,
            [],
            ['groups' => $groups]
        );
    }

    #[Route(path: '/comments/{entity}/reply/{id}', name: 'reply_comment', methods: ['POST'])]
    public function replyComment(
        $entity,
        $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        NotificationRepository $notificationRepository
    ) {

        $originalOne = $this->repo->find($id);
        if (empty($originalOne)) {
            return new JsonResponse(['message' => "Commentaire introuvable."], Response::HTTP_NOT_FOUND);
        }

        if (in_array($entity, self::PARENTS)) {
            $parentGetter = 'get' . ucfirst($entity);
        } else {
            return new JsonResponse(
                ['message' => "Type d'entité invalide."],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            /** @var Comment $comment */
            $comment = $serializer->deserialize(
                $request->getContent(),
                Comment::class,
                'json',
                [
                    'groups' => 'insert:comment',
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => "Erreur lors de la récupération des données"], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var User $user */
        $user = $this->getUser();
        $comment->setOrignalOne($originalOne);
        $comment->setUser($user);
        $comment = $this->repo->insert($comment, false);

        $parentId = $originalOne->$parentGetter()->getId();
        $userNotNotif = [$user->getId()];
        if ($user->getId() != $originalOne->getUser()->getId()) {
            $notification = new Notification();
            $notification->setUser($originalOne->getUser());
            $notification->setNotifier($user);
            $notification->setEntityName($entity);
            $notification->setEntityId($parentId);
            $notification->setIcon('comment');
            $notification->setSubject("En réponse à votre commentaire.");
            $notification->setContent($this->ellipsComment($comment));
            $notificationRepository->insert($notification, false);

            $userNotNotif[] = $originalOne->getUser()->getId();
        }

        foreach ($originalOne->getReplies() as $reply) {
            if (!in_array($reply->getUser()->getId(), $userNotNotif)) {
                $notification = new Notification();
                $notification->setUser($reply->getUser());
                $notification->setNotifier($user);
                $notification->setEntityName($entity);
                $notification->setEntityId($parentId);
                $notification->setIcon('comment');
                $notification->setSubject("A répondu à un commentaire que vous suivez.");
                $notification->setContent($this->ellipsComment($comment));
                $notificationRepository->insert($notification, false);

                $userNotNotif[] = $reply->getUser()->getId();
            }
        }

        $this->em->flush();

        return $this->json(
            ['message' => 'Réponse publiée.', 'comment' => $comment],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:list']]
        );
    }


    #[Route(path: '/comments/{entity}/{id}', name: 'insert_comment', methods: ['POST'])]
    public function insertComment(
        $entity,
        $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        NotificationRepository $notificationRepository
    ) {
        $parentRepo = $this->getParentRepo($entity);
        if (is_null($parentRepo)) {
            return new JsonResponse(
                ['message' => "Mauvaise entité."],
                Response::HTTP_NOT_FOUND
            );
        }

        $parent = $parentRepo->find($id);
        if (empty($parent)) {
            return new JsonResponse(
                ['message' => $this->translateEntity($entity) . " introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        $className = Comment::class;
        if (is_null($className)) {
            return new JsonResponse(
                ['message' => "Mauvaise entité."],
                Response::HTTP_NOT_FOUND
            );
        }

        try {
            /** @var Comment $comment */
            $comment = $serializer->deserialize(
                $request->getContent(),
                $className,
                'json',
                [
                    'groups' => 'insert:comment',
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (in_array($entity, self::PARENTS)) {
            $parentSetter = 'set' . ucfirst($entity);
            $comment->$parentSetter($parent);
        } else {
            return new JsonResponse(
                ['message' => "Type d'entité invalide."],
                Response::HTTP_NOT_FOUND
            );
        }
        /** @var User $user */
        $user = $this->getUser();
        $comment->setUser($user);
        $comment = $this->repo->insert($comment, false);

        $notification = new Notification();
        $notification->setUser($parent->getUser());
        $notification->setNotifier($user);
        $notification->setEntityName($entity);
        $notification->setEntityId($parent->getId());
        $notification->setIcon('comment');
        $notification->setSubject("A commenté votre " . mb_strtolower($this->translateEntity($entity)) . '.');
        $notification->setContent($this->ellipsComment($comment));

        $notificationRepository->insert($notification);

        return $this->json(
            ['message' => 'Commentaire publié.', 'comment' => $comment],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:list']]
        );
    }

    #[Route(path: '/comments/{entity}/comment/{id}', name: 'update_comment', methods: ['PUT'])]
    public function updateComment(
        $entity,
        $id,
        Request $request,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $comment = $this->repo->find($id);
        if (empty($comment)) {
            return new JsonResponse(['message' => "Commentaire introuvable."], Response::HTTP_NOT_FOUND);
        }

        if ($comment->getUser()->getId() !== $this->getUser()->getId()) {
            return new JsonResponse(
                ['message' => "Ce commentaire ne vous appartient pas."],
                Response::HTTP_UNAUTHORIZED
            );
        }

        try {
            $json = json_decode($request->getContent(), true);
        } catch (JsonException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $comment->setContent($json['content']);

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $comment = $this->repo->update($comment);

        return $this->json(
            ['comment' => $comment],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }


    #[Route(path: '/comments/{entity}/comment/{id}', name: 'delete_comment', methods: ['DELETE'])]
    public function deleteComment($entity, $id)
    {
        $comment = $this->repo->find($id);
        if (empty($comment)) {
            return new JsonResponse(['message' => "Commentaire introuvable."], Response::HTTP_NOT_FOUND);
        }

        /** @var User */
        $user = $this->getUser();
        if ($user->getIsModo()) {
            $comment = $this->repo->definitivDelete($comment);
        } else {
            if ($comment->getUser()->getId() !== $user->getId()) {
                return new JsonResponse(
                    ['message' => "Ce commentaire ne vous appartient pas."],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $comment = $this->repo->delete($comment);
        }

        return $this->json(
            [
                'comment' => $comment,
                'message' => "Commentaire supprimé."
            ],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    #[Route(path: '/comments/{entity}/vote/{id}', name: 'vote_team_comment', methods: ['PUT'])]
    public function voteComment(
        $entity,
        $id,
        Request $request,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $comment = $this->repo->find($id);
        if (empty($comment)) {
            return new JsonResponse(['message' => "Commentaire introuvable."], Response::HTTP_NOT_FOUND);
        }

        if ($comment->getDeleted()) {
            return new JsonResponse(['message' => "Vous ne pouvez pas approuver un commentaire supprimé."], Response::HTTP_NOT_FOUND);
        }

        try {
            $json = json_decode($request->getContent(), true);
        } catch (JsonException $e) {
            return new JsonResponse(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        $vote = new Vote();
        $vote->setPositiv($json['positiv']);
        $vote->setUser($this->getUser());
        $userVote = $comment->getUserVote($this->getUser());
        if (!is_null($userVote)) {
            $userVoteIsPositiv = $userVote->getPositiv();
            $comment->removeVote($userVote);
            $comment->setOwnUserVote(null);
            if ($userVoteIsPositiv != $vote->getPositiv()) {
                $comment->addVote($vote);
                $comment->setOwnUserVote($vote);
            }
        } else {
            $comment->addVote($vote);
            $comment->setOwnUserVote($vote);
        }

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }
        $comment = $this->repo->update($comment);

        return $this->json(
            ['comment' => $comment],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }
}

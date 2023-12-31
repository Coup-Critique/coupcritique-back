<?php

namespace App\Controller\Api;

use App\Entity\Notification;
use App\Entity\PokemonInstance;
use App\Entity\Replay;
use App\Entity\Team;
use App\Entity\User;
use App\Normalizer\EntityNormalizer;
use App\Repository\NotificationRepository;
use App\Repository\PokemonRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Service\ErrorManager;
use App\Service\HistoryManager;
use App\Service\PokemonInstanceData;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TeamController extends AbstractController
{
    public function __construct(private readonly TeamRepository $repo)
    {
    }

    #[Route(path: '/teams', name: 'teams', methods: ['GET'])]
    public function getTeams(Request $request)
    {
        if (!empty($request->get('order'))) {
            $this->repo->setOrder($request->get('order'));
            $this->repo->setOrderDirection($request->get('orderDirection'));
        }

        if (!empty($request->get('page'))) {
            $this->repo->setPage($request->get('page'));
        }

        $search = null;
        if (!empty($request->get('search'))) {
            $search = $request->get('search');
        }

        $criteria = [];
        if (!empty($request->get('certified')) || $request->get('certified') === '0') {
            if ($request->get('certified') === 'team_id') {
                $this->repo->setHasTeamId(true);
            } else {
                $criteria['certified'] = $request->get('certified');
            }
        }
        if (!empty($request->get('tier'))) {
            $criteria['tier'] = $request->get('tier');
        } elseif (!empty($request->get('gen'))) {
            $criteria['gen'] = $request->get('gen');
        }

        if (!empty($request->get('tags'))) {
            $criteria['tags'] = explode(',', $request->get('tags'));
        }

        $teams = $this->repo->findWithQuery($criteria, $search);

        $groups = ['read:list:team'];

        /** @var User $user */
        $user = $this->getUser();
        if ($user) {
            foreach ($teams as $team) {
                $team->setIsOwnUserFavorite($user);
            }

            if ($user->getIsModo()) {
                $groups[] = 'read:team:admin';
            }
        }

        return $this->json(
            [
                'teams' => $teams,
                'nbPages' => $this->repo->getNbPages()
            ],
            Response::HTTP_OK,
            [],
            ['groups' => $groups]
        );
    }

    #[Route(path: '/teams/state', name: 'teams_by_state', methods: ['GET'])]
    public function getTeamsByState(Request $request)
    {
        if (!empty($request->get('order'))) {
            $this->repo->setOrder($request->get('order'));
            $this->repo->setOrderDirection($request->get('orderDirection'));
        }

        if (!empty($request->get('page'))) {
            $this->repo->setPage($request->get('page'));
        }

        /** @var User $user */
        $user = $this->getUser();
        $criteria = [];
        if (!empty($request->get('tier'))) {
            $criteria['tier'] = $request->get('tier');
        } elseif (!empty($request->get('gen'))) {
            $criteria['gen'] = $request->get('gen');
        }

        $search = null;
        if (!empty($request->get('search'))) {
            $search = $request->get('search');
        }
        $state = null;
        if (!empty($request->get('state')) || $request->get('state') === '0') {
            $state = $request->get('state');
        }
        if ($state === 'banned' && !$user->getIsModo()) {
            return new JsonResponse(
                ['message' => "Page introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }
        $teams = $this->repo->findByState(
            $state === 'true' ? true : ($state === 'false' ? false : $state),
            $criteria,
            $search
        );
        $groups = ['read:list:team'];
        if ($user && $user->getIsModo()) {
            $groups[] = 'read:team:admin';
        }
        return $this->json(
            [
                'teams' => $teams,
                'nbPages' => $this->repo->getNbPages()
            ],
            Response::HTTP_OK,
            [],
            ['groups' => $groups]
        );
    }

    /**
     * Doit se trouver avant /teams/{id}
     */
    #[Route(path: '/teams/top', name: 'top_week_team', methods: ['GET'])]
    public function getTopTeam()
    {
        return $this->json(
            ['team' => $this->repo->getLastTopWeek()],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:team']]
        );
    }

    /**
     * Modify team attribute top_week to insert current date
     */
    #[IsGranted('ROLE_MODO')]
    #[Route(path: '/teams/top/{id}', name: 'top_week_team_selected', methods: ['PUT'])]
    public function setTopTeamSelected($id, NotificationRepository $notificationRepository)
    {
        $team = $this->repo->find($id);

        if ($team->getBanned()) {
            return new JsonResponse(
                ['message' => "Une équipe bannie ne pas être promue équipe de la semaine."],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!is_null($team->getTopWeek()) && $team->getTopWeek()->format('Y-m-d') === (new \DateTime())->format('Y-m-d')) {
            $team->setTopWeek(null);
            $this->repo->update($team);

            $notification = $notificationRepository->findOneBy([
                'entityName' => 'team',
                'entityId' => $id,
                'subject' => "Félicitation votre équipe a été promue équipe de la semaine !"
            ]);
            if (!is_null($notification)) {
                $notificationRepository->remove($notification, false);
            }
        } else {
            $this->repo->setTopWeek($team);

            $notification = new Notification();
            $notification->setUser($team->getUser());
            $notification->setNotifier($this->getUser());
            $notification->setEntityName('team');
            $notification->setEntityId($id);
            $notification->setColor('blue');
            $notification->setIcon('star');
            $notification->setSubject("Félicitation votre équipe a été promue équipe de la semaine !");
            $notificationRepository->insert($notification);
        }

        return $this->json(
            ['team' => $team],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:team']]
        );
    }

    #[Route(path: '/teams/user/{id}', name: 'team_by_user', methods: ['GET'])]
    public function getTeamsByUser($id, Request $request, UserRepository $userRepository)
    {
        if (!empty($request->get('order'))) {
            $this->repo->setOrder($request->get('order'));
            $this->repo->setOrderDirection($request->get('orderDirection'));
        }

        if (!empty($request->get('page'))) {
            $this->repo->setPage($request->get('page'));
        }

        $user = $userRepository->find($id);
        if (empty($user)) {
            return new JsonResponse(
                ['message' => "Utilisateur introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        if (is_null($this->getUser())) {
            $teams = $this->repo->findbyUser($user, false);
        } else {
            $teams = $this->repo->findbyUser($user, $user->getId() == $this->getUser()->getId());
        }
        return $this->json(
            [
                'teams' => $teams,
                'nbPages' => $this->repo->getNbPages()
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:list:team']]
        );
    }

    #[Route(path: '/teams/pokemons', name: 'team_by_pokemons', methods: ['POST'])]
    public function getTeamByPokemons(Request $request)
    {
        if (!empty($request->get('order'))) {
            $this->repo->setOrder($request->get('order'));
            $this->repo->setOrderDirection($request->get('orderDirection'));
        }

        if (!empty($request->get('page'))) {
            $this->repo->setPage($request->get('page'));
        }

        $json  = json_decode($request->getContent());
        if (!array_key_exists('pokemons', $json) || empty($json['pokemons'])) {
            return new JsonResponse(
                ['message' => "Un ou plusieurs Pokémon sont requis."],
                Response::HTTP_BAD_REQUEST
            );
        }
        $pokemons = $json['pokemons'];
        if (!is_array($pokemons)) {
            return new JsonResponse(
                ['message' => "Le corps de la requête est invalide."],
                Response::HTTP_BAD_REQUEST
            );
        }
        if (count($pokemons) > 6) {
            return new JsonResponse(
                ['message' => "La requête est limité a 6 Pokémon."],
                Response::HTTP_BAD_REQUEST
            );
        }
        // prevent from key sql inject
        $pokemons = array_values($pokemons);
        $teams = $this->repo->findByPokemons($pokemons);
        return $this->json(
            [
                'teams' => $teams,
                'nbPages' => $this->repo->getNbPages()
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:list:team']]
        );
    }

    #[Route(path: '/teams/certified/pokemons/{id}', name: 'team_certified_for_pokemon', methods: ['GET'])]
    public function getTeamByPokemonCertified(
        $id,
        Request $request,
        PokemonRepository $pokemonRepository
    ) {
        if (!empty($request->get('order'))) {
            $this->repo->setOrder($request->get('order'));
            $this->repo->setOrderDirection($request->get('orderDirection'));
        }

        if (!empty($request->get('page'))) {
            $this->repo->setPage($request->get('page'));
        }

        $pokemon = $pokemonRepository->find($id);
        if (empty($pokemon)) {
            return new JsonResponse(
                ['message' => "Pokemon introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        $teams = $this->repo->findCertifiedTeamsByPokemon($pokemon);
        return $this->json(
            [
                'teams' => $teams,
                'nbPages' => $this->repo->getNbPages()
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:list:team']]
        );
    }

    // /**
    //  * @Route("/teams/banned", name="teams_banned", methods={"GET"})
    //  */
    // #[IsGranted('ROLE_MODO')]
    // public function getBannedTeams(Request $request)
    // {
    //     $teams = $this->repo->findBy(
    //         ['banned' => true],
    //         ['date_creation' => 'DESC']
    //     );
    //     return $this->json(
    //         ['teams' => $teams],
    //         Response::HTTP_OK,
    //         [],
    //         ['groups' => ['read:list:team', 'read:team:admin']]
    //     );
    // }
    #[Route(path: '/teams/favorite', name: 'get_favorite_teams', methods: ['GET'])]
    public function getFavoriteTeamForUser(Request $request)
    {
        if (!empty($request->get('order'))) {
            $this->repo->setOrder($request->get('order'));
            $this->repo->setOrderDirection($request->get('orderDirection'));
        }

        if (!empty($request->get('page'))) {
            $this->repo->setPage($request->get('page'));
        }

        /** @var User $user */
        $user = $this->getUser();
        if (is_null($user)) {
            // return ok for loading
            $this->json([], Response::HTTP_OK);
        }
        $teams = $this->repo->findbyFavorites($user);
        foreach ($teams as $team) {
            $team->setIsOwnUserFavorite($user);
        }
        return $this->json(
            [
                'teams' => $teams,
                'nbPages' => $this->repo->getNbPages()
            ],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list:team']
        );
    }

    #[Route(path: '/teams/favorite/{idteam}/{isfavorite}', name: 'add-favorite-team', methods: ['PUT'])]
    public function toggleTeamToFavorite(
        int $idteam,
        string $isfavorite,
        TeamRepository $teamRepository,
        EntityManagerInterface $em
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $team = $teamRepository->find($idteam);
        $isfavorite = Utils::strToBoolean($isfavorite);

        if ($isfavorite) {
            $user->addFavorite($team);
            $message = 'La team a été rajoutée aux favoris';
        } else {
            $user->removeFavorite($team);
            $message = 'La team a été retirée des favoris';
        }

        $em->flush();

        return new JsonResponse(
            ['message' => $message],
            Response::HTTP_OK
        );
    }


    #[Route(path: '/teams/{id}', name: 'team_by_id', methods: ['GET'])]
    public function getTeamById($id)
    {
        $team = $this->repo->findOne($id);

        if (empty($team)) {
            return new JsonResponse(['message' => "Equipe introuvable"], Response::HTTP_NOT_FOUND);
        }

        $groups = ['read:team'];
        /** @var User $user */
        $user = $this->getUser();
        $isModo = false;
        if ($user) {
            $team->setIsOwnUserFavorite($user);
            if ($user->getIsModo()) {
                $groups[] = 'read:team:admin';
                $isModo = true;
            }
        }
        if (
            $team->getBanned()
            && !$isModo
            && (!$user || $user->getId() != $team->getUser()->getId())
        ) {
            return new JsonResponse(['message' => "Equipe introuvable"], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ['team' => $team],
            Response::HTTP_OK,
            [],
            ['groups' => $groups]
        );
    }

    #[Route(path: '/teams/export', name: 'check_team_export', methods: ['POST'])]
    #[Route(path: '/teams/export/{id}', name: 'check_updated_team_export', methods: ['PUT'])]
    public function checkExport(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        PokemonInstanceData $pokemonInstanceData,
        $id = null
    ) {
        $baseTeam = null;
        if (!is_null($id)) {
            $baseTeam = $this->repo->findOne($id);
        }

        try {
            /*** @var Team $team */
            $team = $serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json',
                [
                    'groups' => ['insert:team'],
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                    EntityNormalizer::UPDATE_ENTITIES => [
                        Team::class,
                        PokemonInstance::class
                    ]
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        /** @var PokemonInstance $pokemonInstance */
        foreach ($team->getPokemonInstances() as $pokemonInstance) {
            $pokemonInstanceData->setDataOldGen($pokemonInstance, $team->getGen());
            $pokemonInstance->setTeam($team);
        }

        if (is_null($baseTeam)) {
            /** @var User $user */
            $user = $this->getUser();
            if (!$user->getIsModo()) {
                $teams = $this->repo->findUntreatedbyUser($user);
                foreach ($teams as $oldTeam) {
                    if ($oldTeam->getTier()->getId() === $team->getTier()->getId()) {
                        return $this->json([
                            'message' => 'Vous avez déjà publié une équipe dans ce tier et elle est encore en cours de traitement. Vous devez attendre qu\'elle soit vérifiée par l\'administration avant de pouvoir en reposter une.'
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }
        }

        $errors = $validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $team->setUser($this->getUser());

        return $this->json(
            ['message' => 'Export Showdown correct.', 'team' => $team],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:team']]
        );
    }

    #[Route(path: '/teams', name: 'insert_team', methods: ['POST'])]
    public function insertTeam(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        EntityManagerInterface $em,
        PokemonInstanceData $pokemonInstanceData
    ) {

        try {
            /** @var Team $team */
            $team = $serializer->deserialize(
                $request->getContent(),
                Team::class,
                'json',
                [
                    'groups'                                          => 'insert:team',
                    AbstractObjectNormalizer::SKIP_NULL_VALUES        => true,
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        /** @var PokemonInstance $pokemonInstance */
        foreach ($team->getPokemonInstances() as $pokemonInstance) {
            // $pokemonInstance->setTeam($team);
            $pokemonInstanceData->setDataOldGen($pokemonInstance, $team->getGen());
            $em->persist($pokemonInstance);
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getIsModo()) {
            $teams = $this->repo->findUntreatedbyUser($user);
            foreach ($teams as $oldTeam) {
                if ($oldTeam->getTier()->getId() === $team->getTier()->getId()) {
                    return $this->json([
                        'message' => 'Vous avez déjà publié une équipe dans ce tier et elle est encore en cours de traitement. Vous devez attendre qu\'elle soit vérifiée par l\'administration avant de pouvoir en reposter une.'
                    ], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $errors = $validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $team = $this->repo->insert($team, $this->getUser());

        return $this->json(
            ['message' => 'Equipe publiée.', 'team' => $team],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:team']]
        );
    }

    #[Route(path: '/teams/{id}', name: 'update_team', methods: ['PUT'])]
    public function updateTeam(
        $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        PokemonInstanceData $pokemonInstanceData
    ) {
        $team = $this->repo->findOne($id);
        if (empty($team)) {
            return new JsonResponse(
                ['message' => "Team introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($team->getUser()->getId() !== $user->getId() && !$user->getIsModo()) {
            return new JsonResponse(
                ['message' => "Cette équipe ne vous appartient pas."],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $json = $request->getContent();
        try {
            /** @var Team $team */
            $team = $serializer->deserialize(
                $json,
                Team::class,
                'json',
                [
                    'groups' => $team->getCertified() ? 'update:team' : 'insert:team',
                    AbstractNormalizer::OBJECT_TO_POPULATE            => $team,
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                    EntityNormalizer::UPDATE_ENTITIES => [
                        Team::class,
                        Replay::class,
                        PokemonInstance::class
                    ]
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if (!$team->getCertified()) {
            /** @var PokemonInstance $pokemonInstance */
            foreach ($team->getPokemonInstances() as $pokemonInstance) {
                $pokemonInstanceData->setDataOldGen($pokemonInstance, $team->getGen());
                $pokemonInstance->setTeam($team);
            }
        }

        $errors = $validator->validate($team);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $team = $this->repo->update($team);

        return $this->json(
            ['team' => $team],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:team']
        );
    }


    #[Route(path: '/teams/{id}', name: 'delete_team', methods: ['DELETE'])]
    public function deleteTeam(
        $id,
        EntityManagerInterface $em
    ) {
        $team = $this->repo->findOne($id);
        if (empty($team)) {
            return new JsonResponse(['message' => "Team introuvable."], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->getUser();
        if ($team->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(
                ['message' => "Cette équipe ne vous appartient pas."],
                Response::HTTP_UNAUTHORIZED
            );
        }

        /** @var PokemonInstance $pokemonInstance */
        foreach ($team->getPokemonInstances() as $pokemonInstance) {
            $pokemonInstance->setTeam(null);
            $em->persist($pokemonInstance);
        }
        $em->flush();
        $this->repo->delete($team);

        return new JsonResponse(
            [
                'message' => "Equipe " . $team->getName() . " supprimée.",
                'id'      => $id
            ],
            Response::HTTP_OK
        );
    }

    #[IsGranted('ROLE_MODO')]
    #[Route(path: '/teams/certify/{id}/{certified}', name: 'certify_team', methods: ['PUT'])]
    public function certifyTeam(
        HistoryManager $historyManager,
        NotificationRepository $notificationRepository,
        $id,
        ?string $certified = null
    ) {
        $team = $this->repo->find($id);
        if (empty($team)) {
            return new JsonResponse(['message' => "Equipe introuvable."], Response::HTTP_NOT_FOUND);
        }

        $past = $team->getCertified();
        $certified = Utils::strToBoolean($certified);
        $team->setCertified($certified);
        $historyManager->updateHistory(
            $team,
            $certified ? 'certifié' : 'décertifié',
            $this->getUser()
        );
        $team = $this->repo->update($team);

        $notification = new Notification();
        $notification->setUser($team->getUser());
        if ($certified) {
            $notification->setNotifier($this->getUser());
        }
        $notification->setEntityName('team');
        $notification->setEntityId($id);
        if ($certified) {
            $notification->setColor('green');
            $notification->setIcon('check');
            $notification->setSubject("Félicitation votre équipe a été certifiée !");
        } elseif ($past === true) {
            $notification->setIcon('chevron left');
            $notification->setSubject("La certification de votre équipe " . $team->getName() . " a été retirée.");
            $notification->setContent("Cela est probablement dû au temps et au changement de metagame.");
        } else {
            $notification->setIcon('chevron right');
            $notification->setSubject("Votre équipe a été traitée, vous pouvez de nouveau proposer une équipe.");
        }
        $notificationRepository->insert($notification);

        return $this->json(
            ['certified' => $certified],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:team']
        );
    }

    #[IsGranted('ROLE_MODO')]
    #[Route(path: '/teams/ban/{id}', name: 'ban_team', methods: ['PUT'])]
    public function banTeam(
        HistoryManager $historyManager,
        NotificationRepository $notificationRepository,
        $id
    ) {
        $team = $this->repo->find($id);
        if (empty($team)) {
            return new JsonResponse(['message' => "Equipe introuvable."], Response::HTTP_NOT_FOUND);
        }

        $team->setBanned(!$team->getBanned());
        $historyManager->updateHistory(
            $team,
            $team->getBanned() ? 'bannis' : 'débannis',
            $this->getUser()
        );
        $team = $this->repo->update($team);

        $notification = new Notification();
        $notification->setUser($team->getUser());
        $notification->setEntityName('team');
        $notification->setEntityId($id);
        if ($team->getBanned()) {
            $notification->setColor('red');
            $notification->setIcon('ban');
            $notification->setSubject("Votre équipe a été bannie.");
            $notification->setContent("Veuillez vous référez au commentaire afin de comprendre pourquoi et si c'est remédiable.");
        } else {
            $notification->setColor('orange');
            $notification->setIcon('refresh');
            $notification->setSubject("Félicitation votre équipe a été débannie.");
        }
        $notificationRepository->insert($notification);

        return $this->json(
            ['banned' => $team->getBanned()],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:team']
        );
    }
}

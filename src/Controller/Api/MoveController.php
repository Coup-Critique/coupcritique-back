<?php

namespace App\Controller\Api;

use App\Controller\Api\ContributeControllerInterface;
use App\Entity\Move;
use App\Entity\UsageMove;
use App\Normalizer\EntityNormalizer;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TypeRepository;
use App\Service\EntityMerger;
use App\Service\ErrorManager;
use App\Service\GenRequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MoveController extends AbstractController implements ContributeControllerInterface
{
    private MoveRepository $repo;
    private GenRequestManager $genRequestManager;

    public function __construct(MoveRepository $repo, GenRequestManager $genRequestManager)
    {
        $this->repo = $repo;
        $this->genRequestManager = $genRequestManager;
    }

    /**
     * @Route("/moves", name="moves", methods={"GET"})
     */
    public function getMoves()
    {
        $gen = $this->genRequestManager->getGenFromRequest();
        // only 18 types
        return $this->json(
            ['moves' =>  $this->repo->findBy(['gen' => $gen], ['nom' => 'ASC'])],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    /**
     * @Route("/moves/{id}", name="move_by_id", methods={"GET"})
     */
    public function getMoveById($id, Request $request)
    {
        $move = $this->repo->find($id);

        if (empty($move)) {
            return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $availableGens = $this->genRequestManager->formatAvailableGens(
            $this->repo->getAvailableGens($move->getUsageName())
        );

        return $this->json(
            [
                'move' => $move,
                'gen' => $move->getGen(),
                'availableGens' => $availableGens
            ],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:move', 'read:list']]
        );
    }

    /**
     * @Route("/moves/pokemon/{id}", name="moves_by_pokemon", methods={"GET"})
     */
    public function getMovesByPokemon(
        $id,
        PokemonRepository $pokemonRepository,
        EntityMerger $entityMerger
    ) {
        // TODO Add usage filtering
        $pokemon = $pokemonRepository->find($id);

        if (empty($pokemon)) {
            return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $moves = $this->repo->findByPokemonUsage($pokemon);
        // gen include in id
        return $this->json(
            ['moves' => $entityMerger->merge(
                $moves,
                Move::class,
                ['groups' => ['read:list', 'read:list:usage', 'read:usageMove']],
                fn ($usageMove) => EntityMerger::makeUsageKey(
                    $usageMove,
                    'usageMove',
                    UsageMove::class
                )
            )],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/moves/type/{id}", name="moves_by_type", methods={"GET"})
     */
    public function getMovesByType($id, TypeRepository $typeRepository)
    {
        $type = $typeRepository->find($id);

        if (empty($type)) {
            return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        // gen include in id
        return $this->json(
            ['moves' => $this->repo->findByType($type)],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    /**
     * @Route("/moves", name="insert_move", methods={"POST"})
     */
    public function insertMove(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $json = $request->getContent();
        try {
            /** @var Move $move */
            $move = $serializer->deserialize($json, Move::class, 'json');
        } catch (NotEncodableValueException $e) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $errors = $validator->validate($move);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->repo->insert($move);

        return $this->json(
            ['message' => 'Capacité enregistrée', 'move' => $move],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:move', 'read:list']]
        );
    }

    /**
     * @Route("/moves/{id}", name="update_move", methods={"PUT"})
     */
    public function updateMove(
        $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $move = $this->repo->find($id);
        if (empty($move)) {
            return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();
        try {
            /** @var Move $move */
            $move = $serializer->deserialize(
                $json,
                Move::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $move,
                    EntityNormalizer::UPDATE_ENTITIES => [Move::class]
                ]
            );
        } catch (NotEncodableValueException $e) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($move);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

		$this->repo->update($move, $this->getUser());

        return $this->json(
            ['move' => $move],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:move', 'read:list']]
        );
    }

    /**
     * @Route("/moves/{id}", name="delete_move", methods={"DELETE"})
     */
    public function deleteMove($id, Request $request)
    {
        $move = $this->repo->find($id);
        if (empty($move)) {
            return $this->json(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $this->repo->delete($move);

        return new JsonResponse(
            ['message' => "Capacité $id supprimée", 'id' => $id],
            Response::HTTP_OK
        );
    }
}

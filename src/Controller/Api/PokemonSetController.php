<?php

namespace App\Controller\Api;

use App\Entity\PokemonInstance;
use App\Entity\PokemonSet;
use App\Normalizer\EntityNormalizer;
use App\Repository\PokemonRepository;
use App\Repository\PokemonSetRepository;
use App\Service\DescriptionParser;
use App\Service\StringCollectionDenormalizer;
use App\Service\ErrorManager;
use App\Service\PokemonInstanceData;
use Doctrine\ORM\EntityManagerInterface;
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

class PokemonSetController extends AbstractController implements ContributeControllerInterface
{
    public function __construct(private readonly PokemonSetRepository $repo)
    {
    }

    #[Route(path: '/pokemon_set/{id}', name: 'pokemon_sets_by_pokemon_id', methods: ['GET'])]
    public function getPokemonSetsByPokemonId($id, PokemonRepository $pokemonRepo)
    {
        $pokemon = $pokemonRepo->find($id);
        if (empty($pokemon)) {
            return new JsonResponse(
                ['message' => "Mauvais identifiant"],
                Response::HTTP_NOT_FOUND
            );
        }

        $pokemonSets = $this->repo->findByPokemon($pokemon);

        return $this->json(
            ['pokemonSets' => $pokemonSets],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:pokemon-set', 'read:list', 'read:team']]
        );
    }

    #[Route(path: '/pokemon_set/{id}', name: 'insert_pokemon_set', methods: ['POST'])]
    public function insertPokemonSet(
        int $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        StringCollectionDenormalizer $strCollectionDenormalizer,
        DescriptionParser $descriptionParser,
        EntityManagerInterface $em,
        PokemonInstanceData $pokemonInstanceData
    ) {
        $json = $request->getContent();

        try {
            $pokemonSetArray = json_decode($json, true);
            /** @var PokemonSet $pokemonSet */
            $pokemonSet = $serializer->denormalize(
                $pokemonSetArray,
                PokemonSet::class,
                'json',
                [
                    'groups' => ['update:set', 'insert:team'],
                    AbstractObjectNormalizer::SKIP_NULL_VALUES        => true,
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $strCollectionDenormalizer->denormalize(
            $pokemonSet,
            PokemonSet::class,
            $pokemonSetArray,
            $pokemonSet->getGen()
        );

        $pokemonInstance = $pokemonSet->getInstance();
        $pokemonInstance->setPokemonSet($pokemonSet);
        $pokemonInstanceData->setDataOldGen($pokemonInstance, $pokemonSet->getGen());

        $errors = $validator->validate($pokemonSet);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($pokemonSet->getInstance()->getPokemon()->getId() !== $id) {
            return new JsonResponse(
                ['message' => "Le Pokémon transmis dans l'export n'est pas celui de la page."],
                Response::HTTP_BAD_REQUEST
            );
        }

        $pokemonSet->setContentJson(
            $descriptionParser->parse(
                $pokemonSet->getContent(),
                $pokemonSet->getGen()
            )
        );

        $em->persist($pokemonSet->getInstance());
        $this->repo->insert($pokemonSet, $this->getUser());

        return $this->json(
            ['message' => 'Set enregistré', 'pokemonSet' => $pokemonSet],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:pokemon-set', 'read:list', 'read:team']]
        );
    }

    #[Route(path: '/pokemon_set/{id}', name: 'update_pokemon_set', methods: ['PUT'])]
    public function updatePokemonSet(
        int $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        StringCollectionDenormalizer $strCollectionDenormalizer,
        DescriptionParser $descriptionParser
    ) {
        $pokemonSet = $this->repo->findOneById($id);
        if (empty($pokemonSet)) {
            return new JsonResponse(['message' => "Set introuvable."], Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();

        try {
            $pokemonSetArray = json_decode($json, true);
            /** @var PokemonSet $pokemonSet */
            $pokemonSet = $serializer->denormalize(
                $pokemonSetArray,
                PokemonSet::class,
                'json',
                [
                    'groups' => ['update:set', 'insert:team'],
                    AbstractNormalizer::OBJECT_TO_POPULATE => $pokemonSet,
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                    EntityNormalizer::UPDATE_ENTITIES => [PokemonSet::class, PokemonInstance::class]
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(
            //     ['message' => $e->getMessage()],
            //     Response::HTTP_BAD_REQUEST
            // );
        }

        $strCollectionDenormalizer->denormalize(
            $pokemonSet,
            PokemonSet::class,
            $pokemonSetArray,
            $pokemonSet->getGen()
        );

        $errors = $validator->validate($pokemonSet);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $pokemonSet->setContentJson(
            $descriptionParser->parse(
                $pokemonSet->getContent(),
                $pokemonSet->getGen()
            )
        );
        $this->repo->update($pokemonSet, $this->getUser());

        return $this->json(
            ['message' => 'Set enregistré', 'pokemonSet' => $pokemonSet],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:pokemon-set', 'read:list', 'read:team']]
        );
    }

    #[Route(path: '/pokemon_set/{id}', name: 'delete_pokemon_set', methods: ['DELETE'])]
    public function deletePokemonSet(int $id, EntityManagerInterface $em)
    {
        $pokemonSet = $this->repo->find($id);
        if (empty($pokemonSet)) {
            return new JsonResponse(['message' => "Set introuvable."], Response::HTTP_NOT_FOUND);
        }

        $instance = $pokemonSet->getInstance();
        $instance->setPokemonSet(null);
        $em->persist($instance);
        $em->flush();
        $this->repo->delete($pokemonSet);

        return $this->json(
            ['message' => 'Set supprimé'],
            Response::HTTP_OK,
        );
    }

    #[Route(path: '/pokemon_set/export/{id}', name: 'pokemon_set_export', methods: ['POST'])]
    public function checkPokemonSetExport(
        int $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        PokemonInstanceData $pokemonInstanceData
    ) {
        // TODO mettre l'id d'un Pokémon en parametre pour verifier que l'export concerne bien le bon poke.
        try {
            /** @var PokemonSet $pokemonSet */
            $pokemonSet = $serializer->deserialize(
                $request->getContent(),
                PokemonSet::class,
                'json',
                ['groups' => ['read:team', 'read:pokemon-set', 'read:list']]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        if ($pokemonSet->getInstance()->getPokemon()->getId() !== $id) {
            return new JsonResponse(
                ['message' => "Le Pokémon transmis dans l'export n'est pas celui de la page"],
                Response::HTTP_BAD_REQUEST
            );
        }

        $pokemonInstance = $pokemonSet->getInstance();
        $pokemonInstance->setPokemonSet($pokemonSet);
        $pokemonInstanceData->setDataOldGen($pokemonInstance, $pokemonSet->getGen());

        $errors = $validator->validate($pokemonSet->getInstance());
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            ['message' => 'Export valide', 'instance' => $pokemonSet->getInstance()],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:team']]
        );
    }
}

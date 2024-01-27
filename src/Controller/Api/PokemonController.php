<?php

namespace App\Controller\Api;

use App\Entity\Pokemon;
use App\Entity\UsageAbility;
use App\Entity\UsageItem;
use App\Entity\UsageMove;
use App\Normalizer\EntityNormalizer;
use App\Repository\ItemRepository;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\AbilityRepository;
use App\Repository\TypeRepository;
use App\Service\DescriptionParser;
use App\Service\EntityMerger;
use App\Service\ErrorManager;
use App\Service\GenRequestManager;
use App\Service\PokemonManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PokemonController extends AbstractController implements ContributeControllerInterface
{
    public function __construct(
        private readonly PokemonRepository $repo,
        private readonly GenRequestManager $genRequestManager
    ) {
    }

    #[Route(path: '/pokemons', name: 'pokemons', methods: ['GET'])]
    public function getPokemons()
    {
        $gen = $this->genRequestManager->getGenFromRequest();
        // TODO make a query to order by base form is null first
        return $this->json(
            ['pokemons' =>  $this->repo->findAllWithGen($gen)],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    #[Route(path: '/pokemons/{id}', name: 'pokemon_by_id', methods: ['GET'])]
    public function getPokemonById(
        $id,
        SerializerInterface $serializer,
        PokemonManager $pokemonManager
    ) {
        $pokemon = $this->repo->findOne($id);

        if (empty($pokemon)) {
            return new JsonResponse(['message' => "Pokemon introuvable."], Response::HTTP_NOT_FOUND);
        }

        $availableGens = $this->genRequestManager->formatAvailableGens(
            $this->repo->getAvailableGens($pokemon->getUsageName())
        );

        $return = $pokemonManager->buildOnePokemonReturn($pokemon);
        $return = $serializer->normalize(
            $return,
            'json',
            ['groups' => ['read:pokemon', 'read:usage']]
        );

        $return['gen'] = $pokemon->getGen();
        $return['availableGens'] = $availableGens;

        return $this->json(
            $return,
            Response::HTTP_OK
        );
    }

    #[Route(path: '/pokemon-name/{name}', name: 'pokemon_by_name', methods: ['GET'])]
    public function getPokemonByName(
        $name,
        SerializerInterface $serializer,
        PokemonManager $pokemonManager
    ) {
        $pokemon = $this->repo->findOneByName($name, GenRequestManager::getLastGen());

        if (empty($pokemon)) {
            return new JsonResponse(['message' => "Pokemon introuvable."], Response::HTTP_NOT_FOUND);
        }

        $pokemon = $this->findOneByNameDecreaseGenLoop($pokemon);

        $availableGens = $this->genRequestManager->formatAvailableGens(
            $this->repo->getAvailableGens($pokemon->getUsageName())
        );

        $return = $pokemonManager->buildOnePokemonReturn($pokemon);
        $return = $serializer->normalize(
            $return,
            'json',
            ['groups' => 'read:pokemon']
        );

        $return['gen'] = $pokemon->getGen();
        $return['availableGens'] = $availableGens;

        return $this->json(
            $return,
            Response::HTTP_OK
        );
    }

    public function findOneByNameDecreaseGenLoop(Pokemon $pokemon): Pokemon
    {
        $gen = GenRequestManager::getLastGen();
        while (
            $pokemon->getTier()
            && $pokemon->getTier()->getName() === 'Untiered'
            && $gen > 5
        ) {
            $result = $this->repo->findOneByName($pokemon->getName(), $gen);
            if (empty($result)) break;
            $pokemon = $result;
            $gen--;
        }

        return $pokemon;
    }

    #[Route(path: '/pokemons/type/{id}', name: 'pokemon_by_type', methods: ['GET'])]
    public function getPokemonsByType($id, TypeRepository $typeRepository)
    {
        $type = $typeRepository->find($id);
        if (empty($type)) {
            return new JsonResponse(['message' => "Type introuvable."], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            ['pokemons' => $this->repo->findByType($type)],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    #[Route(path: '/pokemons/ability/{id}', name: 'pokemon_by_ability', methods: ['GET'])]
    public function getPokemonsByAbility(
        $id,
        AbilityRepository $abilityRepository,
        EntityMerger $entityMerger
    ) {
        $ability = $abilityRepository->find($id);

        if (empty($ability)) {
            return new JsonResponse(['message' => "Talent introuvable."], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            [
                'pokemons' => $entityMerger->merge(
                    $this->repo->findByAbility($ability),
                    Pokemon::class,
                    ['groups' => ['read:list:usage', 'read:usageAbility']],
                    fn ($usageAbility) => EntityMerger::makeUsageKey(
                        $usageAbility,
                        'usageAbility',
                        UsageAbility::class
                    )
                )
            ],
            Response::HTTP_OK
        );
    }

    #[Route(path: '/pokemons/move/{id}', name: 'pokemon_by_move', methods: ['GET'])]
    public function getPokemonsByMove(
        $id,
        MoveRepository $moveRepository,
        EntityMerger $entityMerger
    ) {
        $move = $moveRepository->find($id);

        if (empty($move)) {
            return new JsonResponse(
                ['message' => "Capacité introuvable."],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            [
                'pokemons' => $entityMerger->merge(
                    $this->repo->findByMove($move),
                    Pokemon::class,
                    ['groups' => ['read:list:usage', 'read:usageMove']],
                    fn ($usageMove) => EntityMerger::makeUsageKey(
                        $usageMove,
                        'usageMove',
                        UsageMove::class
                    )
                )
            ],
            Response::HTTP_OK
        );
    }

    #[Route(path: '/pokemons/item/{id}', name: 'pokemon_by_item', methods: ['GET'])]
    public function getPokemonsByItem(
        $id,
        ItemRepository $itemRepository,
        EntityMerger $entityMerger
    ) {
        $item = $itemRepository->find($id);

        if (empty($item)) {
            return new JsonResponse(['message' => "Objet introuvable."], Response::HTTP_NOT_FOUND);
        }

        return $this->json(
            [
                'pokemons' => $entityMerger->merge(
                    $this->repo->findByItem($item),
                    Pokemon::class,
                    ['groups' => ['read:list:usage', 'read:usageItem']],
                    fn ($usageItem) => EntityMerger::makeUsageKey(
                        $usageItem,
                        'usageItem',
                        UsageItem::class
                    )
                )
            ],
            Response::HTTP_OK
        );
    }

    #[Route(path: '/pokemons', name: 'insert_pokemon', methods: ['POST'])]
    public function insertPokemon(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $json = $request->getContent();
        try {
            /** @var Pokemon $pokemon */
            $pokemon = $serializer->deserialize(
                $json,
                Pokemon::class,
                'json',
                ['groups' => ['insert:pokemon']]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $errors = $validator->validate($pokemon);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->repo->insert($pokemon);

        return $this->json(
            ['message' => 'Pokémon enregistré.', 'pokemon' => $pokemon],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:pokemon']
        );
    }

    #[Route(path: '/pokemons/{id}', name: 'update_pokemon', methods: ['PUT'])]
    public function updatePokemon(
        $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager,
        DescriptionParser $descriptionParser
    ) {
        $pokemon = $this->repo->find($id);
        if (empty($pokemon)) {
            return new JsonResponse(['message' => "Pokemon introuvable."], Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();
        // TODO remove form from deserialize, decode and denormalize. Add or change forms. Idem for moves
        try {
            /** @var Pokemon $pokemon */
            $pokemon = $serializer->deserialize(
                $json,
                Pokemon::class,
                'json',
                [
                    'groups' => ['insert:pokemon'],
                    AbstractNormalizer::OBJECT_TO_POPULATE => $pokemon,
                    EntityNormalizer::UPDATE_ENTITIES      => [Pokemon::class]
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($pokemon);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $pokemon->setContentJson(
            $descriptionParser->parse(
                $pokemon->getDescription(),
                $pokemon->getGen()
            )
        );

        $this->repo->update($pokemon, $this->getUser());

        return $this->json(
            ['pokemon' => $pokemon],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:pokemon']
        );
    }

    #[Route(path: '/pokemons/{id}', name: 'delete_pokemon', methods: ['DELETE'])]
    public function deletePokemon($id, Request $request)
    {
        $pokemon = $this->repo->find($id);
        if (empty($pokemon)) {
            return new JsonResponse(['message' => "Pokemon introuvable."], Response::HTTP_NOT_FOUND);
        }

        $this->repo->delete($pokemon);

        return new JsonResponse(
            ['message' => "Pokémon $id supprimé"],
            Response::HTTP_OK
        );
    }
}

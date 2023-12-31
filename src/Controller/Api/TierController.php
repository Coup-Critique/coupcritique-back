<?php

namespace App\Controller\Api;

use App\Entity\Tier;
use App\Normalizer\EntityNormalizer;
use App\Repository\TierRepository;
use App\Repository\TierUsageRepository;
use App\Service\ErrorManager;
use App\Service\GenRequestManager;
use App\Service\TierManager;
use App\Service\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TierController extends AbstractController implements ContributeControllerInterface
{
    public function __construct(private readonly TierRepository $repo, private readonly GenRequestManager $genRequestManager)
    {
    }

    #[Route(path: '/tiers', name: 'tiers', methods: ['GET'])]
    public function getTiers()
    {
        $gen = $this->genRequestManager->getGenFromRequest();
        return $this->json(
            ['tiers' => $this->repo->findList($gen)],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    #[Route(path: '/tiers-select', name: 'tiers_select', methods: ['GET'])]
    public function getTiersSelect()
    {
        $tiers = $this->repo->findByPlayableAccrossGens();
        $select = [0 => []];
        // group by gen
        foreach ($tiers as $tier) {
            if ($tier->getOfficial()) {
                $select[0][] = $tier;
            } else {
                $gen = $tier->getGen();
                if (!array_key_exists($gen, $select)) {
                    $select[$gen] = [];
                }
                $select[$gen][] = $tier;
            }
        }
        // krsort($select); => front end break it
        return $this->json(
            ['tiers' => $select],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    #[Route(path: '/tiers/{id}', name: 'tier_by_id', methods: ['GET'])]
    public function getTierById(
        $id,
        TierManager $tierManager,
        SerializerInterface $serializer
    ) {
        $tier = $this->repo->find($id);

        if (empty($tier)) {
            return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $return = $tierManager->getPokemonsFromTier($tier);
        // for group completion separatly
        $return['tier'] = $serializer->normalize($tier, 'json', ['groups' => 'read:tier']);
        $return['gen'] = $tier->getGen();
        $return['availableGens'] = $this->genRequestManager->formatAvailableGens(
            $this->repo->getAvailableGens($tier->getUsageName())
        );

        $return = $serializer->normalize(
            $return,
            'json',
            ['groups' => ['read:list']]
        );

        return $this->json(
            $return,
            Response::HTTP_OK
        );
    }

    #[Route(path: '/tiers/usages/top', name: 'tier_top_usages', methods: ['GET'])]
    public function getTierTopUsages(Request $request, TierUsageRepository $tierUsageRepository)
    {
        $gen = $this->genRequestManager->getGenFromRequest();

        $criteria = [];
        $criteria['official'] = !!Utils::strToBoolean($request->get('official'));
        $criteria['isDouble'] = !!Utils::strToBoolean($request->get('isDouble'));

        $tier = $this->repo->findOneByTopAndGen($gen, $criteria);

        if (empty($tier)) {
            return new JsonResponse(
                ['message' => "Tier introuvable"],
                Response::HTTP_NOT_FOUND
            );
        }

        // gen come from tier ; usage->rank not tier->rank
        $usage = $tierUsageRepository->findOneByFirstRank($tier);

        return $this->json(
            ['tier' => $tier, 'usage' => $usage],
            Response::HTTP_OK,
            [],
            ['groups' => ['read:usage:short']]
        );
    }

    // /**
    //  * @Route("/tiers/gen", name="tiers_by_gen", priority=10, methods={"GET"})
    //  */
    // public function getTiersByGen()
    // {
    //     $tiers_by_gen = array_reduce(
    //         $this->repo->findByPlayable(),
    //         function ($result, $tier) {
    //             $key = "Gen " . $tier->getGen();
    //             if (!array_key_exists($key, $result)) {
    //                 $result[$key] = [$tier];
    //             } else {
    //                 $result[$key][] = $tier;
    //             }
    //             return $result;
    //         },
    //         []
    //     );
    //     return $this->json(
    //         ['tiers' => $tiers_by_gen],
    //         Response::HTTP_OK,
    //         [],
    //         ['groups' => 'read:tier']
    //     );
    // }
    #[Route(path: '/tiers', name: 'insert_tier', methods: ['POST'])]
    public function insertTier(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $json = $request->getContent();
        try {
            /** @var Tier $tier */
            $tier = $serializer->deserialize($json, Tier::class, 'json');
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        $errors = $validator->validate($tier);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->repo->insert($tier);

        return $this->json(
            ['message' => 'Talent enregistré', 'tier' => $tier],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:tier']
        );
    }

    #[Route(path: '/tiers/{id}', name: 'update_tier', methods: ['PUT'])]
    public function updateTier(
        $id,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ErrorManager $errorManager
    ) {
        $tier = $this->repo->find($id);
        if (empty($tier)) {
            return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $json = $request->getContent();
        try {
            /** @var Tier $tier */
            $tier = $serializer->deserialize(
                $json,
                Tier::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $tier,
                    EntityNormalizer::UPDATE_ENTITIES => [Tier::class]
                ]
            );
        } catch (NotEncodableValueException) {
            // return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($tier);
        if (count($errors) > 0) {
            return $this->json(
                ['errors' => $errorManager->parseErrors($errors)],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->repo->insert($tier);

        return $this->json(
            ['tier' => $tier],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:tier']
        );
    }

    #[Route(path: '/tiers/{id}', name: 'delete_tier', methods: ['DELETE'])]
    public function deleteTier($id, Request $request)
    {
        $tier = $this->repo->find($id);
        if (empty($tier)) {
            return $this->json(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
        }

        $this->repo->delete($tier);

        return new JsonResponse(
            ['message' => "Talent $id supprimé", 'id' => $id],
            Response::HTTP_OK
        );
    }
}

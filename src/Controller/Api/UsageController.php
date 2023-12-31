<?php

namespace App\Controller\Api;

use App\Repository\TierUsageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class UsageController extends AbstractController
{

    public function __construct(
        private readonly TierUsageRepository $repo,
        private readonly CacheInterface $usagesCache
    ) {
    }

    #[Route(path: '/usages/{id}', name: 'usage_by_id', methods: ['GET'])]
    public function getOneById($id)
    {

        return $this->usagesCache->get("usage_$id", function () use ($id) {
            $usage = $this->repo->findOne($id);

            if (empty($usage)) {
                return new JsonResponse(['message' => "Usage introuvable."], Response::HTTP_NOT_FOUND);
            }

            return $this->json(
                ['usage' =>  $usage],
                Response::HTTP_OK,
                [],
                ['groups' => 'read:usage']
            );
        });
    }
}

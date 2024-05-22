<?php

namespace App\Controller\Api;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlayerController extends AbstractController
{

    public function __construct(private readonly PlayerRepository $repo)
    {
    }

    #[Route(path: '/players/top', name: 'players_top', methods: ['GET'])]
    public function top()
    {
        $players = $this->repo->findTopPlayers();
        return $this->json(
            ['players' => $players],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }
}

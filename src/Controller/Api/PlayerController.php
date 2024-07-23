<?php

namespace App\Controller\Api;

use Amp\Serialization\Serializer;
use App\Entity\CircuitTour;
use App\Entity\Player;
use App\Repository\CircuitTourRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class PlayerController extends AbstractController
{

    public function __construct(private readonly PlayerRepository $repo)
    {
    }

    #[Route(path: '/players', name: 'all_players', methods: ['GET'])]
    public function all(
        CircuitTourRepository $tourRepo,
        SerializerInterface $serializer
    ) {
        $players = $this->repo->findAll();
        $tours = $tourRepo->findAll();

        /** @var Player $player */
        foreach ($players as $i => $player) {
            $player = $serializer->normalize($player, null, ['groups' => 'read:list']);

            /** @var CircuitTour $tour */
            foreach ($tours as $tour) {
                $scores = $tour->getScores();
                if (!$scores) continue;

                foreach ($scores as $score) {
                    if ($score['player'] === $player['showdown_name']) {
                        $player['scores'][$tour->getId()] = $score;
                        break;
                    }
                }
            }

            $players[$i] = $player;
        }

        return $this->json(
            ['players' => $players, 'circuitTours' => $tours],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
    }

    #[Route(path: '/players/user/{id}', name: 'player_by_user', methods: ['GET'])]
    public function byUser(
        CircuitTourRepository $tourRepo,
        SerializerInterface $serializer,
        int $id
    ) {
        $players = $this->repo->findByUser($id);
        if (empty($players)) {
            return $this->json(
                ['message' => 'Player not found'],
                Response::HTTP_OK
            );
        }
        $tours = $tourRepo->findAll();

        /** @var Player $player */
        foreach ($players as $i => $player) {
            $player = $serializer->normalize($player, null, ['groups' => 'read:list']);

            /** @var CircuitTour $tour */
            foreach ($tours as $tour) {
                $scores = $tour->getScores();
                if (!$scores) continue;

                foreach ($scores as $score) {
                    if ($score['player'] === $player['showdown_name']) {
                        $player['scores'][$tour->getId()] = $score;
                        break;
                    }
                }

                $players[$i] = $player;
            }
        }

        return $this->json(
            ['players' => $players, 'circuitTours' => $tours],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:list']
        );
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

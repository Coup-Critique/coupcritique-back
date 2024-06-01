<?php

namespace App\Controller\Api;

use App\Repository\AbilityRepository;
use App\Repository\GlobalRepository;
use App\Repository\ItemRepository;
use App\Repository\MoveRepository;
use App\Repository\PokemonRepository;
use App\Repository\TierRepository;
use App\Repository\TypeRepository;
use App\Service\GenRequestManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GlobalController extends AbstractController
{
	#[Route(path: '/test', name: 'test')]
	public function test()
	{
		return $this->json(
			['success' => true],
			Response::HTTP_OK
		);
	}

	#[Route(path: '/search/{string}', name: 'search')]
	public function search(
		$string,
		GenRequestManager $genRequestManager,
		PokemonRepository $pokemonRepository,
		MoveRepository $moveRepository,
		TypeRepository $typeRepository,
		AbilityRepository $abilityRepository,
		ItemRepository $itemRepository,
		TierRepository $tierRepository
	) {
		if (empty($string)) {
			return new JsonResponse(
				['message' => "La recherche a besoin d'un mot"],
				Response::HTTP_BAD_REQUEST
			);
		}
		if (strlen($string) < 2) {
			return new JsonResponse(
				['message' => "La recherche doit faire au moins 2 caractères."],
				Response::HTTP_BAD_REQUEST
			);
		}

		if (str_contains($string, '-')) {
			$string = str_replace('-', ' ', $string);
		}

		$gen       = $genRequestManager->getGenFromRequest();
		$pokemons  = $pokemonRepository->search($string, $gen);
		$moves     = $moveRepository->search($string, $gen);
		$types     = $typeRepository->search($string, $gen);
		$abilities = $abilityRepository->search($string, $gen);
		$items     = $itemRepository->search($string, $gen);
		$tiers     = $tierRepository->search($string, $gen);

		// TODO make a read:name group
		return $this->json(
			[
				'pokemons'  => $pokemons,
				'moves'     => $moves,
				'types'     => $types,
				'abilities' => $abilities,
				'items'     => $items,
				'tiers'     => $tiers
			],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list', 'read:own:list']]
		);
	}

	#[Route(path: '/search/previews/{string}', name: 'previews')]
	public function getPreviews(
		$string,
		GlobalRepository $globalRepository
	) {
		if (empty($string)) {
			return new JsonResponse(
				['message' => "La recherche a besoin d'un mot"],
				Response::HTTP_BAD_REQUEST
			);
		}

		$gen      = GenRequestManager::getLastGen();
		$previews = $globalRepository->search($string, $gen);

		return $this->json(
			["previews" => $previews],
			Response::HTTP_OK,
			[],
			['groups' => 'read:name']
		);
	}
}

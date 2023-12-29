<?php

namespace App\Controller\Api;

use App\Entity\Ability;
use App\Normalizer\EntityNormalizer;
use App\Repository\AbilityRepository;
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

class AbilityController extends AbstractController
{
	private AbilityRepository $repo;
	private GenRequestManager $genRequestManager;

	public function __construct(AbilityRepository $repo, GenRequestManager $genRequestManager)
	{
		$this->repo = $repo;
		$this->genRequestManager = $genRequestManager;
	}

	/**
	 * @Route("/abilities", name="abilities", methods={"GET"})
	 */
	public function getAbilities()
	{
		$gen = $this->genRequestManager->getGenFromRequest();
		// no need join select
		return $this->json(
			['abilities' => $this->repo->findBy(['gen' => $gen], ['nom' => 'ASC'])],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list', 'read:own:list']]
		);
	}

	/**
	 * @Route("/abilities/{id}", name="ability_by_id", methods={"GET"})
	 */
	public function getAbilityById($id, Request $request)
	{
		// gen include in id
		$ability = $this->repo->find($id);

		if (empty($ability)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$availableGens = $this->genRequestManager->formatAvailableGens(
			$this->repo->getAvailableGens($ability->getUsageName())
		);

		return $this->json(
			[
				'ability' => $ability,
				'gen' => $ability->getGen(), 
				'availableGens' => $availableGens
			],
			Response::HTTP_OK,
			[],
			['groups' => 'read:ability']
		);
	}

	/**
	 * @Route("/abilities", name="insert_ability", methods={"POST"})
	 */
	public function insertAbility(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$json = $request->getContent();
		try {
			/** @var Ability $ability */
			$ability = $serializer->deserialize($json, Ability::class, 'json');
		} catch (NotEncodableValueException $e) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
		$errors = $validator->validate($ability);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($ability);

		return $this->json(
			['message' => 'Talent enregistré', 'ability' => $ability],
			Response::HTTP_OK,
			[],
			['groups' => 'read:ability']
		);
	}

	/**
	 * @Route("/abilities/{id}", name="update_ability", methods={"PUT"})
	 */
	public function updateAbility(
		$id,
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$ability = $this->repo->find($id);
		if (empty($ability)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$json = $request->getContent();
		try {
			/** @var Ability $ability */
			$ability = $serializer->deserialize(
				$json,
				Ability::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $ability,
					EntityNormalizer::UPDATE_ENTITIES => [Ability::class]
				]
			);
		} catch (NotEncodableValueException $e) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		$errors = $validator->validate($ability);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->update($ability, $this->getUser());

		return $this->json(
			['ability' => $ability],
			Response::HTTP_OK,
			[],
			['groups' => 'read:ability']
		);
	}

	/**
	 * @Route("/abilities/{id}", name="delete_ability", methods={"DELETE"})
	 */
	public function deleteAbility($id, Request $request)
	{
		$ability = $this->repo->find($id);
		if (empty($ability)) {
			return $this->json(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$this->repo->delete($ability);

		return new JsonResponse(
			['message' => "Talent $id supprimé", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

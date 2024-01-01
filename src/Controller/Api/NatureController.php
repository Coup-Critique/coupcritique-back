<?php

namespace App\Controller\Api;

use App\Entity\Nature;
use App\Normalizer\EntityNormalizer;
use App\Repository\NatureRepository;
use App\Service\ErrorManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NatureController extends AbstractController
{
	public function __construct(private readonly NatureRepository $repo)
	{
	}

	#[Route(path: '/natures', name: 'natures', methods: ['GET'])]
	public function getNatures()
	{
		return $this->json(
			['natures' =>  $this->repo->findAll()],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	#[Route(path: '/natures/{id}', name: 'nature_by_id', methods: ['GET'])]
	public function getNatureById($id, Request $request)
	{
		$nature = $this->repo->find($id);

		if (empty($nature)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		return $this->json(
			['nature' => $nature],
			Response::HTTP_OK,
			[],
			['groups' => 'read:nature']
		);
	}

	#[Route(path: '/natures', name: 'insert_nature', methods: ['POST'])]
	public function insertNature(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$json = $request->getContent();
		try {
			/** @var Nature $nature */
			$nature = $serializer->deserialize($json, Nature::class, 'json');
		} catch (NotEncodableValueException) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
		$errors = $validator->validate($nature);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($nature);

		return $this->json(
			['message' => 'Nature enregistrée', 'nature' => $nature],
			Response::HTTP_OK,
			[],
			['groups' => 'read:nature']
		);
	}

	#[Route(path: '/natures/{id}', name: 'update_nature', methods: ['PUT'])]
	public function updateNature(
		$id,
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$nature = $this->repo->find($id);
		if (empty($nature)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$json = $request->getContent();
		try {
			/** @var Nature $nature */
			$nature = $serializer->deserialize(
				$json,
				Nature::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $nature,
					EntityNormalizer::UPDATE_ENTITIES => [Nature::class]
				]
			);
		} catch (NotEncodableValueException) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		$errors = $validator->validate($nature);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($nature);

		return $this->json(
			['nature' => $nature],
			Response::HTTP_OK,
			[],
			['groups' => 'read:nature']
		);
	}

	#[Route(path: '/natures/{id}', name: 'delete_nature', methods: ['DELETE'])]
	public function deleteNature($id, Request $request)
	{
		$nature = $this->repo->find($id);
		if (empty($nature)) {
			return $this->json(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$this->repo->delete($nature);

		return new JsonResponse(
			['message' => "Nature $id supprimée", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

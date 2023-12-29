<?php

namespace App\Controller\Api;

use App\Entity\Type;
use App\Entity\Weakness;
use App\Normalizer\EntityNormalizer;
use App\Repository\TypeRepository;
use App\Repository\WeaknessRepository;
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

class TypeController extends AbstractController
{
	private TypeRepository $repo;
	private GenRequestManager $genRequestManager;

	public function __construct(TypeRepository $repo, GenRequestManager $genRequestManager)
	{
		$this->genRequestManager = $genRequestManager;
		$this->repo = $repo;
	}

	/**
	 * Types chart
	 * @Route("/types", name="types", methods={"GET"})
	 */
	public function getTypes(SerializerInterface $serializer)
	{
		// $gen = $this->genRequestManager->getGenFromRequest();
		// $types = $this->repo->findAllByGen($gen);
		// $normalized_types = $serializer->normalize($types, 'json', ['groups' => 'read:type']);
		// // Set weaknesses by type attacker id
		// foreach ($normalized_types as $i => &$type) {
		// 	$weaknesses = [];
		// 	foreach ($type['weaknesses'] as $j => $weakness) {
		// 		$weaknesses[$types[$i]->getWeaknesses()[$j]->getTypeAttacker()->getId()] = $weakness;
		// 	}
		// 	$type['weaknesses'] = $weaknesses;
		// }
		// return $this->json(
		// 	['types' => $normalized_types],
		// 	Response::HTTP_OK
		// );
		$gen = $this->genRequestManager->getGenFromRequest();
		return $this->json(
			['types' => $this->repo->findAllByGen($gen)],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	/**
	 * @Route("/types/{id}", name="type_by_id", methods={"GET"})
	 */
	public function getTypeById($id, SerializerInterface $serializer)
	{
		$type = $this->repo->find($id);

		if (empty($type)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$availableGens = $this->genRequestManager->formatAvailableGens(
			$this->repo->getAvailableGens($type->getName())
		);

		return $this->json(
			[
				'type' => $type,
				'weaknesses' => $serializer->normalize(
					$type->getWeaknesses(),
					'json',
					['groups' => 'read:weakness']
				),
				'efficiencies' => $serializer->normalize(
					$type->getEfficiencies(),
					'json',
					['groups' => 'read:weakness']
				),
				'gen' => $type->getGen(),
				'availableGens' => $availableGens
			],
			Response::HTTP_OK,
			[],
			['groups' => 'read:type']
		);
	}

	/**
	 * @Route("/types", name="insert_type", methods={"POST"})
	 */
	public function insertType(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$json = $request->getContent();
		try {
			/** @var Type $type */
			$type = $serializer->deserialize($json, Type::class, 'json');
		} catch (NotEncodableValueException $e) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
		$errors = $validator->validate($type);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($type);

		return $this->json(
			['message' => 'Type posted', 'type' => $type],
			Response::HTTP_OK,
			[],
			['groups' => 'read:type']
		);
	}

	/**
	 * @Route("/types/{id}", name="update_type", methods={"PUT"})
	 */
	public function updateType(
		$id,
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$type = $this->repo->find($id);
		if (empty($type)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$json = $request->getContent();
		try {
			/** @var Type $type */
			$type = $serializer->deserialize(
				$json,
				Type::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $type,
					EntityNormalizer::UPDATE_ENTITIES => [Type::class]
				]
			);
		} catch (NotEncodableValueException $e) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		$errors = $validator->validate($type);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($type);

		return $this->json(
			['type' => $type],
			Response::HTTP_OK,
			[],
			['groups' => 'read:type']
		);
	}

	/**
	 * @Route("/types/{id}", name="delete_type", methods={"DELETE"})
	 */
	public function deleteType($id, Request $request, TypeRepository $type_repo)
	{
		$type = $this->repo->find($id);
		if (empty($type)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$this->repo->delete($type);

		return new JsonResponse(
			['message' => "Type $id deleted", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

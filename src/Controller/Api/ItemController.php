<?php

namespace App\Controller\Api;

use App\Entity\Item;
use App\Normalizer\EntityNormalizer;
use App\Repository\ItemRepository;
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

class ItemController extends AbstractController
{
	public function __construct(private readonly ItemRepository $repo, private readonly GenRequestManager $genRequestManager)
	{
	}

	#[Route(path: '/items', name: 'items', methods: ['GET'])]
	public function getItems()
	{
		$gen = $this->genRequestManager->getGenFromRequest();
		// no need join select
		return $this->json(
			['items' =>  $this->repo->findBy(['gen' => $gen], ['nom' => 'ASC'])],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list', 'read:own:list']]
		);
	}

	#[Route(path: '/items/{id}', name: 'item_by_id', methods: ['GET'])]
	public function getItemById($id, Request $request)
	{
		// gen include in id
		$item = $this->repo->find($id);

		if (empty($item)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$availableGens = $this->genRequestManager->formatAvailableGens(
			$this->repo->getAvailableGens($item->getUsageName())
		);

		return $this->json(
			[
				'item' => $item,
				'gen' => $item->getGen(),
				'availableGens' => $availableGens
			],
			Response::HTTP_OK,
			[],
			['groups' => 'read:item']
		);
	}

	#[Route(path: '/items', name: 'insert_item', methods: ['POST'])]
	public function insertItem(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$json = $request->getContent();
		try {
			/** @var Item $item */
			$item = $serializer->deserialize($json, Item::class, 'json');
		} catch (NotEncodableValueException) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}
		$errors = $validator->validate($item);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->insert($item);

		return $this->json(
			['message' => 'Objet enregistré', 'item' => $item],
			Response::HTTP_OK,
			[],
			['groups' => 'read:item']
		);
	}

	#[Route(path: '/items/{id}', name: 'update_item', methods: ['PUT'])]
	public function updateItem(
		$id,
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$item = $this->repo->find($id);
		if (empty($item)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$json = $request->getContent();
		try {
			/** @var Item $item */
			$item = $serializer->deserialize(
				$json,
				Item::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $item,
					EntityNormalizer::UPDATE_ENTITIES => [Item::class]
				]
			);
		} catch (NotEncodableValueException) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		$errors = $validator->validate($item);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$this->repo->update($item, $this->getUser());

		return $this->json(
			['item' => $item],
			Response::HTTP_OK,
			[],
			['groups' => 'read:item']
		);
	}

	#[Route(path: '/items/{id}', name: 'delete_item', methods: ['DELETE'])]
	public function deleteItem($id, Request $request)
	{
		$item = $this->repo->find($id);
		if (empty($item)) {
			return $this->json(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$this->repo->delete($item);

		return new JsonResponse(
			['message' => "Objet $id supprimé", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

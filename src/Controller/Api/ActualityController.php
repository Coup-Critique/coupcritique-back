<?php

namespace App\Controller\Api;

use App\Entity\Actuality;
use App\Normalizer\EntityNormalizer;
use App\Repository\ActualityRepository;
use App\Service\DescriptionParser;
use App\Service\ErrorManager;
use App\Service\ImageArticleManager;
use App\Service\GenRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ActualityController extends AbstractController implements ContributeControllerInterface
{
	final public const ACTUALITY_IMAGE_SIZE        = 300;
	final public const ACTUALITY_TEASER_IMAGE_SIZE = 250;

	public function __construct(private readonly ActualityRepository $repo)
	{
	}

	#[Route(path: '/actualities', name: 'actualities', methods: ['GET'])]
	public function getActualities(Request $request)
	{
		if (!empty($request->get('maxLength'))) {
			$actualities = $this->repo->findWithMax($request->get('maxLength'));
		} else {
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$actualities = $this->repo->findWithQuery($criteria);
		}

		return $this->json(
			['actualities' => $actualities],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	#[Route(path: '/actualities/{id}', name: 'actuality_by_id', methods: ['GET'], priority: -1)]
	public function getActualityById($id)
	{
		$actuality = $this->repo->findOne($id);

		if (empty($actuality)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		return $this->json(
			['actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article', 'read:name']]
		);
	}

	#[Route(path: '/actualities/{id}/images', name: 'actuality_images', methods: ['POST'])]
	public function setActualityImages(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager,
		EntityManagerInterface $em
	) {
		$actuality = $this->repo->findOne($id);

		if (empty($actuality)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		if (!count($request->files)) {
			return $this->json(
				['message' => 'Aucune pièce fournie.'],
				Response::HTTP_BAD_REQUEST
			);
		}

		$imageArticleManager->setImagesToEntity($actuality, $request->files, 'actualities');

		$em->flush();

		return $this->json(
			['actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/actualities', name: 'insert_actuality', methods: ['POST'])]
	public function insertActuality(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$json = $request->getContent();
		try {
			/** @var Actuality $actuality */
			$actuality = $serializer->deserialize($json, Actuality::class, 'json');
		} catch (NotEncodableValueException) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($actuality);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$actuality->setParsedDescription(
			$descriptionParser->parseToWysiwyg(
				$actuality->getDescription(),
				$genRequestManager->getGenFromRequest()
			)
		);
		$this->repo->insert($actuality, $this->getUser());

		return $this->json(
			['message' => 'Actualité enregistrée', 'actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/actualities/{id}', name: 'update_actuality', methods: ['PUT'])]
	public function updateActuality(
		$id,
		Request $request,
		EntityManagerInterface $em,
		ImageArticleManager $imageArticleManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$actuality = $this->repo->findOne($id);
		if (empty($actuality)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$originalImages      = $actuality->getImages();
		$originalDescription = $actuality->getDescription();

		$json = $request->getContent();
		try {
			/** @var Actuality $actuality */
			$actuality = $serializer->deserialize(
				$json,
				Actuality::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $actuality,
					EntityNormalizer::UPDATE_ENTITIES => [Actuality::class]
				]
			);
		} catch (NotEncodableValueException) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($actuality);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($originalDescription !== $actuality->getDescription()) {
			$actuality->setParsedDescription(
				$descriptionParser->parseToWysiwyg(
					$actuality->getDescription(),
					$genRequestManager->getGenFromRequest()
				)
			);
		}


		$imageArticleManager->removeImagesFromEntity($actuality, 'actualities', $originalImages);

		$actuality->setUpdateDate(new \DateTime());
		$em->flush();

		return $this->json(
			['message' => 'Actualité mise à jour', 'actuality' => $actuality],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/actualities/{id}', name: 'delete_actuality', methods: ['DELETE'])]
	public function deleteActuality($id, ImageArticleManager $imageArticleManager)
	{
		$actuality = $this->repo->find($id);
		if (empty($actuality)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$imageArticleManager->removeImagesFromEntity($actuality, 'actualities');

		$this->repo->delete($actuality);

		return new JsonResponse(
			['message' => "Actualité $id supprimée", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

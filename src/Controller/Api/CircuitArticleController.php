<?php

namespace App\Controller\Api;

use App\Entity\CircuitArticle;
use App\Normalizer\EntityNormalizer;
use App\Repository\CircuitArticleRepository;
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

class CircuitArticleController extends AbstractController implements ContributeControllerInterface
{
	final public const TOURNAMENT_IMAGE_SIZE        = 300;
	final public const TOURNAMENT_TEASER_IMAGE_SIZE = 250;

	public function __construct(private readonly CircuitArticleRepository $repo)
	{
	}

	#[Route(path: '/circuit-articles', name: 'circuit-articles', methods: ['GET'])]
	public function getCircuitArticles(Request $request)
	{
		if (!empty($request->get('maxLength'))) {
			$circuitArticles = $this->repo->findWithMax($request->get('maxLength'));
		} else {
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$circuitArticles = $this->repo->findWithQuery($criteria);
		}

		return $this->json(
			['circuitArticles' => $circuitArticles],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}


	#[Route(path: '/circuit-articles/{id}', name: 'circuit-article_by_id', methods: ['GET'])]
	public function getCircuitArticleById($id)
	{
		$circuitArticle = $this->repo->findOne($id);

		if (empty($circuitArticle)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		return $this->json(
			['circuitArticle' => $circuitArticle],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article', 'read:name']]
		);
	}

	#[Route(path: '/circuit-articles/{id}/images', name: 'circuit-article_images', methods: ['POST'])]
	public function setCircuitArticleImages(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager,
		EntityManagerInterface $em
	) {
		$circuitArticle = $this->repo->findOne($id);

		if (empty($circuitArticle)) {
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

		$imageArticleManager->setImagesToEntity($circuitArticle, $request->files, 'circuit-articles');

		$em->flush();

		return $this->json(
			['circuitArticle' => $circuitArticle],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/circuit-articles', name: 'insert_circuit-article', methods: ['POST'])]
	public function insertCircuitArticle(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$json = $request->getContent();
		try {
			/** @var CircuitArticle $circuitArticle */
			$circuitArticle = $serializer->deserialize($json, CircuitArticle::class, 'json');
		} catch (NotEncodableValueException) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($circuitArticle);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$circuitArticle->setParsedDescription(
			$descriptionParser->parseToWysiwyg(
				$circuitArticle->getDescription(),
				$genRequestManager->getGenFromRequest()
			)
		);
		$this->repo->insert($circuitArticle, $this->getUser());

		return $this->json(
			['message' => 'Article enregistré', 'circuitArticle' => $circuitArticle],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/circuit-articles/{id}', name: 'update_circuit-article', methods: ['PUT'])]
	public function updateCircuitArticle(
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
		$circuitArticle = $this->repo->findOne($id);
		if (empty($circuitArticle)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$originalImages      = $circuitArticle->getImages();
		$originalDescription = $circuitArticle->getDescription();

		$json = $request->getContent();
		try {
			/** @var CircuitArticle $circuitArticle */
			$circuitArticle = $serializer->deserialize(
				$json,
				CircuitArticle::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $circuitArticle,
					EntityNormalizer::UPDATE_ENTITIES => [CircuitArticle::class]
				]
			);
		} catch (\Exception $e) {
			return $this->json(
				['message' => $e->getMessage()],
				Response::HTTP_BAD_REQUEST
			);
		}

		$errors = $validator->validate($circuitArticle);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($originalDescription !== $circuitArticle->getDescription()) {
			$circuitArticle->setParsedDescription(
				$descriptionParser->parseToWysiwyg(
					$circuitArticle->getDescription(),
					$genRequestManager->getGenFromRequest()
				)
			);
		}

		$imageArticleManager->removeImagesFromEntity($circuitArticle, 'circuit-articles', $originalImages);

		$circuitArticle->setUpdateDate(new \DateTime());
		$em->flush();

		return $this->json(
			['message' => 'Article mis à jour', 'circuitArticle' => $circuitArticle],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/circuit-articles/{id}', name: 'delete_circuit-article', methods: ['DELETE'])]
	public function deleteCircuitArticle($id, ImageArticleManager $imageArticleManager)
	{
		$circuitArticle = $this->repo->find($id);
		if (empty($circuitArticle)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$imageArticleManager->removeImagesFromEntity($circuitArticle, 'circuit-articles');

		$this->repo->delete($circuitArticle);

		return new JsonResponse(
			['message' => "Article $id supprimé", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

<?php

namespace App\Controller\Api;

use App\Entity\Guide;
use App\Entity\Resource;
use App\Normalizer\EntityNormalizer;
use App\Repository\GuideRepository;
use App\Repository\ResourceRepository;
use App\Service\DescriptionParser;
use App\Service\ErrorManager;
use App\Service\ImageArticleManager;
use App\Service\GenRequestManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GuideController extends AbstractController implements ContributeControllerInterface
{
	private GuideRepository $repo;

	public function __construct(GuideRepository $repo)
	{
		$this->repo = $repo;
	}

	/**
	 * @Route("/guides", name="guides", methods={"GET"})
	 */
	public function getGuides(Request $request)
	{
		if (!empty($request->get('maxLength'))) {
			$guides = $this->repo->findWithMax($request->get('maxLength'));
		} else {
			$search = null;
			if (!empty($request->get('search'))) {
				$search = $request->get('search');
			}

			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}

			$guides = $this->repo->findWithQuery($criteria, $search);
		}


		return $this->json(
			['guides' => $guides],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	/**
	 * @Route("/guides/{id}", name="guide_by_id", methods={"GET"})
	 */
	public function getGuideById($id)
	{
		$guide = $this->repo->findOne($id);

		if (empty($guide)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		return $this->json(
			['guide' => $guide],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article', 'read:name', 'read:resource']]
		);
	}

	/**
	 * @Route("/guides/{id}/images", name="guide_images", methods={"POST"})
	 */
	public function setGuideImages(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager
	) {
		$guide = $this->repo->findOne($id);

		if (empty($guide)) {
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

		$imageArticleManager->setImagesToEntity($guide, $request->files, 'guides');

		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['guide' => $guide],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/guides", name="insert_guide", methods={"POST"})
	 */
	public function insertGuide(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$json = $request->getContent();
		try {
			/** @var Guide $guide */
			$guide = $serializer->deserialize($json, Guide::class, 'json');
		} catch (NotEncodableValueException $e) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($guide);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$guide->setParsedDescription(
			$descriptionParser->parseToWysiwyg(
				$guide->getDescription(),
				$genRequestManager->getGenFromRequest()
			)
		);

		$resource = $guide->getResource();

		if ($resource) {
			$resource->setTitle($guide->getTitle());
			$resource->setCategory(trim($resource->getCategory()));
			$resource->setUrl('tmp');
		}

		$this->repo->insert($guide, $this->getUser());

		if ($resource) {
			$guide->getResource()->setUrl(
				$this->generateUrl(
					'home',
					['reactRouting' => 'entity/guides/' . $guide->getId()],
					UrlGeneratorInterface::ABSOLUTE_URL
				)
			);
		}

		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['message' => 'Guide enregistré', 'guide' => $guide],
			Response::HTTP_CREATED,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/guides/{id}", name="update_guide", methods={"PUT"})
	 */
	public function updateGuide(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager,
		ResourceRepository $resourceRepository
	) {
		/** @var Guide $guide */
		$guide = $this->repo->findOne($id);
		if (empty($guide)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$originalImages      = $guide->getImages();
		$originalDescription = $guide->getDescription();
		$originalResource    = $guide->getResource();

		$json = $request->getContent();
		try {
			/** @var Guide $guide */
			$guide = $serializer->deserialize(
				$json,
				Guide::class,
				'json',
				[
					'groups' => ['read:article', 'read:resource'],
					AbstractNormalizer::OBJECT_TO_POPULATE => $guide,
					AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
					EntityNormalizer::UPDATE_ENTITIES => [Guide::class, Resource::class]
				]
			);
		} catch (NotEncodableValueException $e) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}
		$errors = $validator->validate($guide);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($originalDescription !== $guide->getDescription()) {
			$guide->setParsedDescription(
				$descriptionParser->parseToWysiwyg(
					$guide->getDescription(),
					$genRequestManager->getGenFromRequest()
				)
			);
		}

		$imageArticleManager->removeImagesFromEntity($guide, 'guides', $originalImages);

		$resource = $guide->getResource();
		if ($originalResource && $resource == null)
			$resourceRepository->delete($originalResource);

		if ($resource) {
			$resource->setTitle($guide->getTitle());
			$resource->setCategory(trim($resource->getCategory()));
			$resource->setUrl($this->generateUrl(
				'home',
				['reactRouting' => 'entity/guides/' . $guide->getId()],
				UrlGeneratorInterface::ABSOLUTE_URL
			));
		}

		$guide->setUpdateDate(new \DateTime());
		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['message' => 'Guide mis à jour', 'guide' => $guide],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article', 'read:resource']]
		);
	}

	/**
	 * @Route("/guides/{id}", name="delete_guide", methods={"DELETE"})
	 */
	public function deleteGuide($id, ImageArticleManager $imageArticleManager)
	{
		$guide = $this->repo->find($id);
		if (empty($guide)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$imageArticleManager->removeImagesFromEntity($guide, 'guides');

		$this->repo->delete($guide);

		return new JsonResponse(
			['message' => "Guide $id supprimé", 'id' => $id],
			Response::HTTP_OK
		);
	}
}
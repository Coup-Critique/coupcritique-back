<?php

namespace App\Controller\Api;

use App\Entity\CircuitTour;
use App\Normalizer\EntityNormalizer;
use App\Repository\CircuitTourRepository;
use App\Service\CalendarMaker;
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

class CircuitTourController extends AbstractController implements ContributeControllerInterface
{
	final public const TOURNAMENT_IMAGE_SIZE        = 300;
	final public const TOURNAMENT_TEASER_IMAGE_SIZE = 250;

	public function __construct(private readonly CircuitTourRepository $repo)
	{
	}

	#[Route(path: '/circuit-tours', name: 'circuit-tours', methods: ['GET'])]
	public function getCircuitTours(
		Request $request,
		SerializerInterface $serializer
	) {
		if (!empty($request->get('maxLength'))) {
			$circuitTours = $this->repo->findWithMax($request->get('maxLength'));
		} else {
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$circuitTours = $this->repo->findWithQuery($criteria);
		}

		return $this->json(
			['circuitTours' => $circuitTours],
			Response::HTTP_OK,
			[],
			[
				'groups' => 'read:list',
				AbstractNormalizer::CALLBACKS => [
					'pokemon' => fn ($p) => $serializer->normalize($p, null, ['groups' => 'read:name']),
				],
			]
		);
	}

	#[Route(path: '/circuit-tours/calendar', name: 'circuit-tours_calendar', methods: ['GET'])]
	public function circuitCalendar(
		CalendarMaker $calendarMaker,
		SerializerInterface $serializer
	) {
		$circuitTours = $this->repo->findForCalendar();
		$currentTours  = [];
		$dateNow = new \DateTime();
		foreach ($circuitTours as $circuitTour) {
			if ($circuitTour->getStartDate() <= $dateNow && $circuitTour->getEndDate() >= $dateNow) {
				$currentTours[] = $circuitTour;
			}
			if ($circuitTour->getStartDate() > $dateNow) {
				break;
			}
		}
		$currentTours = array_reverse($currentTours);

		return $this->json(
			['calendar' => $calendarMaker->makeCalendar($circuitTours), 'currentTours' => array_slice($currentTours, 0, 2)],
			Response::HTTP_OK,
			[],
			[
				'groups' => 'read:list',
				AbstractNormalizer::CALLBACKS => [
					'pokemon' => fn ($p) => $serializer->normalize($p, null, ['groups' => 'read:name']),
				],
			]
		);
	}

	#[Route(path: '/circuit-tours/{id}', name: 'circuit-tour_by_id', methods: ['GET'], priority: -1)]
	public function getCircuitTourById($id)
	{
		$circuitTour = $this->repo->findOne($id);

		if (empty($circuitTour)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		return $this->json(
			['circuitTour' => $circuitTour],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article', 'read:name']]
		);
	}

	#[Route(path: '/circuit-tours/{id}/images', name: 'circuit-tour_images', methods: ['POST'])]
	public function setCircuitTourImages(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager,
		EntityManagerInterface $em
	) {
		$circuitTour = $this->repo->findOne($id);

		if (empty($circuitTour)) {
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

		$errors = $imageArticleManager->setImagesToEntity($circuitTour, $request->files, 'circuit-tours');

		$em->flush();

		return $this->json(
			['circuitTour' => $circuitTour, 'errors' => $errors],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/circuit-tours', name: 'insert_circuit-tour', methods: ['POST'])]
	public function insertCircuitTour(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$json = $request->getContent();
		try {
			/** @var CircuitTour $circuitTour */
			$circuitTour = $serializer->deserialize($json, CircuitTour::class, 'json');
		} catch (NotEncodableValueException) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($circuitTour);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$circuitTour->setParsedDescription(
			$descriptionParser->parseToWysiwyg(
				$circuitTour->getDescription(),
				$genRequestManager->getGenFromRequest()
			)
		);
		$this->repo->insert($circuitTour, $this->getUser());

		return $this->json(
			['message' => 'Tournoi enregistré', 'circuitTour' => $circuitTour],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/circuit-tours/{id}', name: 'update_circuit-tour', methods: ['PUT'])]
	public function updateCircuitTour(
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
		$circuitTour = $this->repo->findOne($id);
		if (empty($circuitTour)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$originalImages      = $circuitTour->getImages();
		$originalDescription = $circuitTour->getDescription();

		$json = $request->getContent();
		try {
			/** @var CircuitTour $circuitTour */
			$circuitTour = $serializer->deserialize(
				$json,
				CircuitTour::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $circuitTour,
					EntityNormalizer::UPDATE_ENTITIES => [CircuitTour::class]
				]
			);
		} catch (\Exception $e) {
			return $this->json(
				['message' => $e->getMessage()],
				Response::HTTP_BAD_REQUEST
			);
		}

		$errors = $validator->validate($circuitTour);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($originalDescription !== $circuitTour->getDescription()) {
			$circuitTour->setParsedDescription(
				$descriptionParser->parseToWysiwyg(
					$circuitTour->getDescription(),
					$genRequestManager->getGenFromRequest()
				)
			);
		}

		$imageArticleManager->removeImagesFromEntity($circuitTour, 'circuit-tours', $originalImages);

		$circuitTour->setUpdateDate(new \DateTime());
		$em->flush();

		return $this->json(
			['message' => 'Tournoi mis à jour', 'circuitTour' => $circuitTour],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	#[Route(path: '/circuit-tours/{id}', name: 'delete_circuit-tour', methods: ['DELETE'])]
	public function deleteCircuitTour($id, ImageArticleManager $imageArticleManager)
	{
		$circuitTour = $this->repo->find($id);
		if (empty($circuitTour)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$imageArticleManager->removeImagesFromEntity($circuitTour, 'circuit-tours');

		$this->repo->delete($circuitTour);

		return new JsonResponse(
			['message' => "Tournoi $id supprimé", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

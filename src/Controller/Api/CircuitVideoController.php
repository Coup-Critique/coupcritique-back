<?php

namespace App\Controller\Api;

use App\Entity\CircuitVideo;
use App\Normalizer\EntityNormalizer;
use App\Repository\CircuitVideoRepository;
use App\Service\CircuitTourJoiner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class CircuitVideoController extends AbstractController implements ContributeControllerInterface
{
	public function __construct(private readonly CircuitVideoRepository $repo)
	{
	}

	#[Route(path: '/circuit-videos', name: 'circuit_videos', methods: ['GET'])]
	public function getAll(Request $request): Response
	{
		if (!empty($request->get('maxLength'))) {
			$circuitVideos = $this->repo->findWithMax($request->get('maxLength'));
		} else {
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$author = $request->get('author');
			$circuitVideos = $this->repo->findWithQuery($criteria, $author);
		}
		return $this->json(
			['circuitVideos' => $circuitVideos],
			Response::HTTP_OK,
			[],
			['groups' => 'read:video']
		);
	}

	#[Route(path: '/circuit-videos/tour/{id}', name: 'circuit_videos_by_tour', methods: ['GET'])]
	public function getByTour($id, Request $request): Response
	{
		if (!empty($request->get('maxLength'))) {
			$circuitVideos = $this->repo->findWithMax($request->get('maxLength'), $id);
		} else {
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$author = $request->get('author');
			$circuitVideos = $this->repo->findByTour($id, $criteria, $author);
		}

		return $this->json(
			['circuitVideos' => $circuitVideos],
			Response::HTTP_OK,
			[],
			['groups' => 'read:video']
		);
	}

	#[Route(path: '/circuit-videos', name: 'insert_circuit_video', methods: ['POST'])]
	public function insertCircuitVideo(
		Request $request,
		CircuitTourJoiner $circuitTourJoiner,
		SerializerInterface $serializer
	) {
		$json = $request->getContent();
		try {
			/** @var CircuitVideo $video */
			$video = $serializer->deserialize(
				$json,
				CircuitVideo::class,
				'json',
				['groups' => ['insert:video']]
			);
		} catch (NotEncodableValueException) {
		}

		$circuitTourJoiner->joinTour($video, $json);

		$this->repo->insert($video);
		return $this->json(
			['message' => 'Vidéo enregistrée', 'circuitVideo' => $video],
			Response::HTTP_OK,
			[],
			['groups' => 'read:video']
		);
	}

	#[Route(path: '/circuit-videos/{id}', name: 'update_circuit_video', methods: ['PUT'])]
	public function updateCircuitVideo(
		$id,
		Request $request,
		CircuitTourJoiner $circuitTourJoiner,
		SerializerInterface $serializer
	) {
		$json = $request->getContent();
		try {
			$video = $this->repo->find($id);
			$video = $serializer->deserialize(
				$json,
				CircuitVideo::class,
				'json',
				[
					'groups' => 'insert:video',
					AbstractNormalizer::OBJECT_TO_POPULATE => $video,
					EntityNormalizer::UPDATE_ENTITIES      => [CircuitVideo::class]
				]
			);
		} catch (NotEncodableValueException) {
		}

		$circuitTourJoiner->joinTour($video, $json);
		$this->repo->update($video);

		return new JsonResponse(
			['message' => "Vidéo $id mise à jour", 'id' => $id],
			Response::HTTP_OK
		);
	}

	#[Route(path: '/circuit-videos/{id}', name: 'delete_circuit_video', methods: ['DELETE'])]
	public function deleteCircuitVideo($id)
	{
		$video = $this->repo->find($id);
		if (empty($video)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$this->repo->delete($video);

		return new JsonResponse(
			['message' => "Vidéo $id supprimée", 'id' => $id],
			Response::HTTP_OK
		);
	}

	#[Route(path: '/circuit-videos/authors', name: 'circuit_videos_authors', methods: ['GET'])]
	public function getAllAuthors()
	{
		return $this->json(
			['authors' => $this->repo->findAllAuthors()],
			Response::HTTP_OK,
			[],
			['groups' => 'read:video']
		);
	}
}

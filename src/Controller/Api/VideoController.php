<?php

namespace App\Controller\Api;

use App\Entity\Video;
use App\Normalizer\EntityNormalizer;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class VideoController extends AbstractController implements ContributeControllerInterface
{
	public function __construct(private readonly VideoRepository $repo)
	{
	}

	#[Route(path: '/videos', name: 'videos', methods: ['GET'])]
	public function getAll(Request $request): Response
	{
		if (!empty($request->get('maxLength')))
			$this->repo->setMaxLength($request->get('maxLength'));

		$criteria = null;
		if (!empty($request->get('tags')))
			$criteria = explode(',', $request->get('tags'));

		$author = $request->get('author');
		return $this->json(
			['videos' => $this->repo->findWithQuery($criteria, $author)],
			Response::HTTP_OK,
			[],
			['groups' => 'read:video']
		);
	}

	#[Route(path: '/videos', name: 'insert_video', methods: ['POST'])]
	public function insertVideo(
		Request $request,
		SerializerInterface $serializer
	) {
		$json = $request->getContent();
		try {
			/** @var Video $video */
			$video = $serializer->deserialize(
				$json,
				Video::class,
				'json',
				['groups' => ['insert:video']]
			);

			$this->repo->insert($video);
		} catch (NotEncodableValueException) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		return $this->json(
			['message' => 'Vidéo enregistrée', 'video' => $video],
			Response::HTTP_OK,
			[],
			['groups' => 'read:video']
		);
	}

	#[Route(path: '/videos/{id}', name: 'update_video', methods: ['PUT'])]
	public function updateVideo(
		$id,
		Request $request,
		SerializerInterface $serializer
	) {
		$json = $request->getContent();
		try {
			$video = $this->repo->find($id);
			$newVideo = $serializer->deserialize(
				$json,
				Video::class,
				'json',
				[
					'groups' => 'insert:video',
					AbstractNormalizer::OBJECT_TO_POPULATE => $video,
					EntityNormalizer::UPDATE_ENTITIES      => [Video::class]
				]
			);
			$this->repo->update($newVideo);
		} catch (NotEncodableValueException) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		return new JsonResponse(
			['message' => "Vidéo $id mise à jour", 'id' => $id],
			Response::HTTP_OK
		);
	}

	#[Route(path: '/videos/{id}', name: 'delete_video', methods: ['DELETE'])]
	public function deleteVideo($id)
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

	#[Route(path: '/videos/authors', name: 'videos_authors', methods: ['GET'])]
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

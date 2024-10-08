<?php

namespace App\Controller\Api;

use App\Repository\DrivedFileRepository;
use App\Service\FileManager;
use App\Service\ImageArticleManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DrivedFileController extends AbstractController implements ContributeControllerInterface
{
	final public const IMAGE_SIZE = 1078;

	public function __construct(private readonly DrivedFileRepository $repo)
	{
	}

	#[Route(path: '/drive', name: 'drive', methods: ['GET'])]
	public function getFiles(Request $request)
	{
		$files = $this->repo->findBy([], ['id' => 'DESC']);

		return $this->json(
			['files' => $files],
			Response::HTTP_OK,
		);
	}


	#[Route(path: '/drive', name: 'drive_post_files', methods: ['POST'])]
	public function setDrivedFileImages(Request $request, ImageArticleManager $imageArticleManager)
	{
		if (!count($request->files)) {
			return $this->json(
				['message' => 'Aucune pièce fournie.'],
				Response::HTTP_BAD_REQUEST
			);
		}

		[$images, $errors] = $imageArticleManager->upload($request->files, 'drive', self::IMAGE_SIZE);

		$files = $this->repo->save($images);

		return $this->json(
			['message' => 'Fichier sauvegardé', 'files' => $files, 'errors' => $errors],
			Response::HTTP_OK
		);
	}

	#[Route(path: '/drive/{id}', name: 'delete_file', methods: ['DELETE'])]
	public function deleteFile($id, FileManager $fileManager)
	{
		$file = $this->repo->find($id);
		if (empty($file)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$fileManager->remove("images/drive/" . $file->getFilename());

		$this->repo->remove($file);

		return new JsonResponse(
			['message' => "Fichier $id supprimée", 'id' => $id],
			Response::HTTP_OK
		);
	}
}

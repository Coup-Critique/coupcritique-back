<?php

namespace App\Controller\Api;

use App\Entity\Tournament;
use App\Normalizer\EntityNormalizer;
use App\Repository\TournamentRepository;
use App\Service\DescriptionParser;
use App\Service\ErrorManager;
use App\Service\ImageArticleManager;
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

class TournamentController extends AbstractController implements ContributeControllerInterface
{
	const TOURNAMENT_IMAGE_SIZE        = 300;
	const TOURNAMENT_TEASER_IMAGE_SIZE = 250;

	/** @var TournamentRepository */
	private $repo;

	public function __construct(TournamentRepository $repo)
	{
		$this->repo = $repo;
	}

	/**
	 * @Route("/tournaments", name="tournaments", methods={"GET"})
	 */
	public function getTournaments(Request $request)
	{
		if (!empty($request->get('maxLength'))) {
			$tournaments = $this->repo->findWithMax($request->get('maxLength'));
		}else{
			$criteria = null;
			if (!empty($request->get('tags'))) {
				$criteria = explode(',', $request->get('tags'));
			}
			$tournaments = $this->repo->findWithQuery($criteria);
		}

		return $this->json(
			['tournaments' => $tournaments],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	/**
	 * @Route("/tournaments/{id}", name="tournament_by_id", methods={"GET"})
	 */
	public function getTournamentById($id)
	{
		$tournament = $this->repo->findOne($id);

		if (empty($tournament)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		return $this->json(
			['tournament' => $tournament],
			Response::HTTP_OK,
			[],
			['groups' => ['read:article','read:name']]
		);
	}

	/**
	 * @Route("/tournaments/{id}/images", name="tournament_images", methods={"POST"})
	 */
	public function setTournamentImages($id, Request $request, ImageArticleManager $imageArticleManager)
	{
		$tournament = $this->repo->findOne($id);

		if (empty($tournament)) {
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

        $imageArticleManager->setImagesToEntity($tournament, $request->files,'tournaments');

		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['tournament' => $tournament],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/tournaments", name="insert_tournament", methods={"POST"})
	 */
	public function insertTournament(
		Request $request,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$json = $request->getContent();
		try {
			/** @var Tournament $tournament */
			$tournament = $serializer->deserialize($json, Tournament::class, 'json');
		} catch (NotEncodableValueException $e) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($tournament);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		$tournament->setParsedDescription(
			$descriptionParser->parseToWysiwyg(
				$tournament->getDescription(),
				$genRequestManager->getGenFromRequest()
			)
		);
		$this->repo->insert($tournament,$this->getUser());

		return $this->json(
			['message' => 'Tournoi enregistré', 'tournament' => $tournament],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/tournaments/{id}", name="update_tournament", methods={"PUT"})
	 */
	public function updateTournament(
		$id,
		Request $request,
		ImageArticleManager $imageArticleManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager,
		DescriptionParser $descriptionParser,
		GenRequestManager $genRequestManager
	) {
		$tournament = $this->repo->findOne($id);
		if (empty($tournament)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$originalImages      = $tournament->getImages();
		$originalDescription = $tournament->getDescription();

		$json = $request->getContent();
		try {
			/** @var Tournament $tournament */
			$tournament = $serializer->deserialize(
				$json,
				Tournament::class,
				'json',
				[
					AbstractNormalizer::OBJECT_TO_POPULATE => $tournament,
					EntityNormalizer::UPDATE_ENTITIES => [Tournament::class]
				]
			);
		} catch (NotEncodableValueException $e) {
			// return $this->json(
			// 	['message' => $e->getMessage()],
			// 	Response::HTTP_BAD_REQUEST
			// );
		}

		$errors = $validator->validate($tournament);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($originalDescription !== $tournament->getDescription()) {
			$tournament->setParsedDescription(
				$descriptionParser->parseToWysiwyg(
					$tournament->getDescription(),
					$genRequestManager->getGenFromRequest()
				)
			);
		}

        $imageArticleManager->removeImagesFromEntity($tournament,'tournaments', $originalImages);

		$tournament->setUpdateDate(new \DateTime());
		$this->getDoctrine()->getManager()->flush();

		return $this->json(
			['message' => 'Tournoi mis à jour', 'tournament' => $tournament],
			Response::HTTP_OK,
			[],
			['groups' => 'read:article']
		);
	}

	/**
	 * @Route("/tournaments/{id}", name="delete_tournament", methods={"DELETE"})
	 */
	public function deleteTournament($id, ImageArticleManager $imageArticleManager)
	{
		$tournament = $this->repo->find($id);
		if (empty($tournament)) {
			return $this->json(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

        $imageArticleManager->removeImagesFromEntity($tournament,'tournaments');

		$this->repo->delete($tournament);

		return new JsonResponse(
			['message' => "Tournoi $id supprimé", 'id' => $id],
			Response::HTTP_OK
		);
	}
}
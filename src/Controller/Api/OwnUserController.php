<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Normalizer\EntityNormalizer;
use App\Repository\UserRepository;
use App\Service\ErrorManager;
use App\Service\FileManager;
use App\Service\RefreshTokenManager;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OwnUserController extends AbstractController
{
	final public const USER_IMAGE_DIR = 'images/uploads/users/';
	final public const USER_IMAGE_SIZE      = 455;
	final public const USER_MINI_IMAGE_SIZE = 200;

	public function __construct(private readonly UserRepository $repo)
	{
	}

	#[Route(path: '/own-user', name: 'own_user', methods: ['GET'])]
	public function getOwnUser()
	{
		return $this->json(
			['user' => $this->getUser()],
			Response::HTTP_OK,
			[],
			UserController::getContext() + ['groups' => ['read:user', 'read:user:own']]
		);
	}

	#[Route(path: '/own-user', name: 'delete_own_user', methods: ['DELETE'])]
	public function deleteOwnUser(FileManager $fileManager)
	{
		/** @var User $user */
		$user = $this->getUser();
		if ($picture = $user->getPicture()) {
			$fileManager->remove(self::USER_IMAGE_DIR . $picture);
			$fileManager->remove(self::USER_IMAGE_DIR . self::USER_MINI_IMAGE_SIZE . "px/$picture");
		}

		$this->repo->delete($user);
		return new JsonResponse(
			['message' => "Votre compte a été supprimé"],
			Response::HTTP_OK
		);
	}

	#[Route(path: '/own-user', name: 'update_own_user', methods: ['PUT'])]
	public function updateUser(
		Request $request,
		EntityManagerInterface $em,
		RefreshTokenManager $refreshTokenManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager
	) {
		$json = $request->getContent();
		try {
			/** @var User $user */
			$user = $serializer->deserialize(
				$json,
				User::class,
				'json',
				[
					'groups' => 'insert:user',
					AbstractNormalizer::OBJECT_TO_POPULATE => $this->getUser(),
					EntityNormalizer::UPDATE_ENTITIES      => [User::class]
				]
			);
		} catch (NotEncodableValueException) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		$errors = $validator->validate($user);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if (
			$user->getUsername() !== $this->getUser()->getUserIdentifier()
			&& !empty($this->repo->findOneByUsername($user->getUsername()))
		) {
			return new JsonResponse(
				['message' => 'Ce nom utilisateur est déjà utilisé'],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}

		if (
			$user->getEmail() !== $this->getUser()->getEmail()
			&& !empty($this->repo->findOneByEmail($user->getEmail()))
		) {
			return new JsonResponse(
				['message' => 'Cette email est déjà utilisée.'],
				Response::HTTP_INTERNAL_SERVER_ERROR
			);
		}

		$user  = $this->repo->update($user);
		$em->flush();

		[$token, $refreshToken] = $refreshTokenManager->create($user);

		return $this->json(
			[
				'message' => "Compte mis à jour",
				'user' => $user,
				'token' => $token,
				'refreshToken' => $refreshToken
			],
			Response::HTTP_OK,
			[],
			UserController::getContext() + ['groups' => ['read:user', 'read:user:own']]
		);
	}

	#[Route(path: '/own-user/password', name: 'update_password', methods: ['PUT'])]
	public function updatePassword(
		Request $request,
		JWTTokenManagerInterface $JWTManager
	) {
		try {
			$data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return new JsonResponse(
				['message' => $e->getMessage()],
				Response::HTTP_BAD_REQUEST
			);
		}

		$user = $this->getUser();

		if (empty($data['old_password']) || !$this->repo->checkPassword($user, $data['old_password'])) {
			return new JsonResponse(['message' => 'Mauvais mot de passe'], Response::HTTP_BAD_REQUEST);
		}

		if (empty($data['confirmation']) || $data['confirmation'] !== $data['new_password']) {
			return new JsonResponse(['message' => 'Mauvais confirmation de mot de passe'], Response::HTTP_BAD_REQUEST);
		}

		if (empty($data['new_password'])) {
			return new JsonResponse(['message' => 'Un nouveau mot de passe est requis'], Response::HTTP_BAD_REQUEST);
		}

		$user = $this->repo->updatePassword($user, $data['new_password']);
		// don't need to regenerate RefreshToken
		$token = $JWTManager->create($user);
		return new JsonResponse(
			[
				'message' => "Mot de passe mis à jour",
				'token' => $token
			],
			Response::HTTP_OK
		);
	}

	/**
	 * We should use POST instead of PUT due to $_FILE upload support
	 */
	#[Route(path: '/own-user/picture', name: 'update_picture', methods: ['POST'])]
	public function updatePicture(
		Request $request,
		FileManager $fileManager,
		EntityManagerInterface $em
	) {
		if (!$request->files->has('picture')) {
			return $this->json(
				['message' => 'Image non fournie.'],
				Response::HTTP_BAD_REQUEST
			);
		}

		/** @var User $user */
		$user = $this->getUser();

		$picture  = $request->files->get('picture');
		$fileName = $fileManager->upload($picture, self::USER_IMAGE_DIR);
		$filePath = self::USER_IMAGE_DIR . $fileName;
		$fileMiniPath = $fileManager->copy(
			$filePath,
			self::USER_IMAGE_DIR . self::USER_MINI_IMAGE_SIZE . 'px'
		);

		// resize image
		try {
			$fileManager->resize($filePath, self::USER_IMAGE_SIZE);
			$fileManager->resize($fileMiniPath, self::USER_MINI_IMAGE_SIZE);
			$fileManager->imageCropCenter($fileMiniPath);
		} catch (\Exception) {
			$fileManager->remove($filePath);
			return $this->json(
				['message' => "Le format de l'image est invalide ou elle est trop lourde."],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($pastPicture = $user->getPicture()) {
			$fileManager->remove(self::USER_IMAGE_DIR . $pastPicture);
			$fileManager->remove(self::USER_IMAGE_DIR . self::USER_MINI_IMAGE_SIZE . "px/$pastPicture");
		}
		$user->setPicture($fileName);
		$em->flush();

		return $this->json(
			['picture' => $user->getPicture(), 'message' => 'Image enregistrée'],
			Response::HTTP_CREATED
		);
	}
}

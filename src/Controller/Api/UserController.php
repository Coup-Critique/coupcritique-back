<?php

namespace App\Controller\Api;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\ActivateUserTokenRepository;
use App\Repository\PasswordTokenRenewRepository;
use App\Repository\CommentRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\ErrorManager;
use App\Service\FileManager;
use App\Service\HistoryManager;
use App\Service\CcMailer;
use Doctrine\ORM\EntityManagerInterface;
use JsonException;
// use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
	private UserRepository $repo;

	public function __construct(UserRepository $repo)
	{
		$this->repo = $repo;
	}

	/**
	 * @Route("/users", name="register", methods={"POST"})
	 */
	public function register(
		Request $request,
		// JWTTokenManagerInterface $JWTManager,
		SerializerInterface $serializer,
		ValidatorInterface $validator,
		ErrorManager $errorManager //,
		// CcMailer $mailer,
		// ActivateUserTokenRepository $activateUserTokenRepository
	) {
		$json = $request->getContent();
		try {
			/** @var User $user */
			$user = $serializer->deserialize(
				$json,
				User::class,
				'json',
				['groups' => ['insert:user']]
			);
		} catch (NotEncodableValueException $e) {
			// return $this->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
		}

		$errors = $validator->validate($user);
		if (count($errors) > 0) {
			return $this->json(
				['errors' => $errorManager->parseErrors($errors)],
				Response::HTTP_BAD_REQUEST
			);
		}

		if (!empty($this->repo->findOneByUsername($user->getUsername()))) {
			return new JsonResponse(
				['message' => 'Ce nom utilisateur est déjà utilisé.'],
				Response::HTTP_FORBIDDEN
			);
		}

		if (!empty($this->repo->findOneByEmail($user->getEmail()))) {
			return new JsonResponse(
				['message' => 'Cette email est déjà utilisée.'],
				Response::HTTP_FORBIDDEN
			);
		}

		if ($this->repo->ipIsBanned($request->getClientIp()) || $this->repo->likeBanned($user)) {
			return new JsonResponse(
				['message' => 'Vous avez été banni, vous n\'avez plus la permission de vous inscrire.'],
				Response::HTTP_FORBIDDEN
			);
		}

		$user = $this->repo->insert($user);

		// $token = $JWTManager->create($user);

		// $activateUserToken = $activateUserTokenRepository->createToken($user);

		// $email = (new TemplatedEmail())
		// 	->from(new Address("contact@coupcritique.fr", "CoupCritique"))
		// 	->to($user->getEmail())
		// 	->subject('Lien d\'activation pour votre compte sur coupcritique.fr')
		// 	->htmlTemplate('emails/user-activation.html.twig')
		// 	->context([
		// 		'user'  => $user,
		// 		'token' => $activateUserToken->getToken()
		// 	]);

		// $mailer->send($email);

		return $this->json(
			[
				// 'message' => "Vous êtes inscrit, un lien d'activation de votre compte vous a été envoyé par mail.<br/> 
				// Vous ne pourrez pas vous connecter tant qu'il ne sera pas rempli.",
				'message' => "Vous êtes inscrit, vous pouvez désormais vous connecter.",
				'user' => $user/* , 'token' => $token */
			],
			Response::HTTP_OK,
			[],
			['groups' => 'read:user']
		);
	}

	/**
	 * @Route("/users", name="users", methods={"GET"})
	 */
	public function getUsers(Request $request)
	{
		// if (!empty($request->get('order'))) {
		// 	$this->repo->setOrder($request->get('order'));
		// 	$this->repo->setOrderDirection($request->get('orderDirection'));
		// }

		// if (!empty($request->get('page'))) {
		// 	$this->repo->setPage($request->get('page'));
		// }

		// $search = null;
		// if (!empty($request->get('search'))) {
		// 	$search = $request->get('search');
		// }

		$groups = ['read:list'];
		if ($this->getUser() && $this->getUser()->getIsModo()) {
			$groups[] = 'read:user:admin';
		}
		return $this->json(
			['users' => $this->repo->findAll()],
			Response::HTTP_OK,
			[],
			['groups' => $groups]
		);
	}

	/**
	 * @Route("/users/admin", name="users_admin", methods={"GET"})
	 * @IsGranted("ROLE_MODO")
	 */
	public function getUsersForAdmin(Request $request)
	{

		// if (!empty($request->get('order'))) {
		// 	$this->repo->setOrder($request->get('order'));
		// 	$this->repo->setOrderDirection($request->get('orderDirection'));
		// }

		// if (!empty($request->get('page'))) {
		// 	$this->repo->setPage($request->get('page'));
		// }

		$search = null;
		if (!empty($request->get('search'))) {
			$search = $request->get('search');
		}

		return $this->json(
			['users' => $this->repo->findAllForAdmin($search)],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list', 'read:user:admin']]
		);
	}

	/**
	 * @Route("/users/{id}", name="user", methods={"GET"})
	 */
	public function getUserById($id, CommentRepository $tcRepo)
	{
		$user = $this->repo->find($id);

		if (empty($user)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$nbComments = $tcRepo->countByUser($user);

		$groups = ['read:user'];
		$isModo = false;
		if ($this->getUser() && $this->getUser()->getIsModo()) {
			$groups[] = 'read:user:admin';
			$isModo = true;
		}
		if (($user->getDeleted() || $user->getBanned()) && !$isModo) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		return $this->json(
			['user' => $user, 'nbComments' => $nbComments ?: 0],
			Response::HTTP_OK,
			[],
			['groups' => $groups]
		);
	}

	/**
	 * @Route("/users/picture/{id}", name="delete_user_picture", methods={"DELETE"})
	 * @IsGranted("ROLE_MODO")
	 */
	public function deleteUserPicture(
		$id,
		HistoryManager $historyManager,
		FileManager $fileManager
	) {
		$user = $this->repo->find($id);

		if (empty($user)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$fileManager->remove($user->getPicture());
		$user->setPicture(null);
		$historyManager->updateHistory(
			$user,
			'image de profil supprimée',
			$this->getUser()
		);
		$user = $this->repo->update($user);

		return new JsonResponse(
			[
				'message' => "L'image de profil de " . $user->getUsername() . " a été supprimée.",
				'user'  => $user
			],
			Response::HTTP_OK
		);
	}

	/**
	 * @Route("/users/{id}", name="delete_user", methods={"DELETE"})
	 * @IsGranted("ROLE_MODO")
	 */
	public function deleteUser($id, HistoryManager $historyManager)
	{
		$user = $this->repo->find($id);

		if (empty($user)) {
			return new JsonResponse(
				['message' => "Mauvais identifiant"],
				Response::HTTP_NOT_FOUND
			);
		}

		$historyManager->updateHistory($user, 'supprimé', $this->getUser());
		$this->repo->delete($user);

		$username = $user->getUsername();
		return new JsonResponse(
			['message' => "Utilisateur $username {id: $id} supprimé"],
			Response::HTTP_OK
		);
	}

	/**
	 * @Route("/users/ban/{id}", name="ban_user", methods={"PUT"})
	 * @IsGranted("ROLE_MODO")
	 */
	public function banUser($id, HistoryManager $historyManager)
	{
		$user = $this->repo->find($id);

		if (empty($user)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		$user->setBanned(!$user->getBanned());
		$historyManager->updateHistory(
			$user,
			$user->getBanned() ? 'bannis' : 'débannis',
			$this->getUser()
		);
		$user = $this->repo->update($user);

		return new JsonResponse(
			[
				'message' => "Utilisateur " . $user->getUsername() . " bannis",
				'banned'  => $user->getBanned()
			],
			Response::HTTP_OK
		);
	}

	/**
	 * @Route("/users/modo/{id}", name="make_user_modo", methods={"PUT"})
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function makeUserModo(
		$id,
		HistoryManager $historyManager,
		NotificationRepository $notificationRepository
	) {
		$user = $this->repo->find($id);

		if (empty($user)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}
		if ($user->getIsAdmin()) {
			return new JsonResponse(
				['message' => "L'utilisateur est administrateur"],
				Response::HTTP_BAD_REQUEST
			);
		}

		if ($user->getIsModo()) {
			$roles = $user->getRoles();
			$index = array_search(User::ROLE_MODO, $roles);
			if ($index !== false) {
				array_splice($roles, $index, 1);
			}
			$user->setRoles($roles);
			$historyManager->updateHistory(
				$user,
				'retrait du rôle modérateur',
				$this->getUser()
			);
		} else {
			$roles = $user->getRoles();
			$roles[] = User::ROLE_MODO;
			$user->setRoles($roles);
			$historyManager->updateHistory(
				$user,
				'rendu modérateur',
				$this->getUser()
			);

			$notification = new Notification();
			$notification->setUser($user);
			$notification->setEntityName('user');
			$notification->setEntityId($id);
			$notification->setColor('purple');
			$notification->setIcon('gem');
			$notification->setSubject("Vous avez été rendu modérateur");
			$notificationRepository->insert($notification, false);
		}

		$user = $this->repo->update($user);

		return new JsonResponse(
			[
				'message' => "Utilisateur " . $user->getUsername()
					. ($user->getIsModo() ? " a obtenu" : "a perdu")
					. " le rôle modérateur.",
				'is_modo'  => $user->getIsModo()
			],
			Response::HTTP_OK
		);
	}

	/**
	 * @Route("/users/tiper/{id}", name="make_user_tiper", methods={"PUT"})
	 * @IsGranted("ROLE_ADMIN")
	 */
	public function makeUserTiper(
		$id,
		HistoryManager $historyManager,
		NotificationRepository $notificationRepository
	) {
		$user = $this->repo->find($id);

		if (empty($user)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		if ($user->getIsTiper()) {
			$user->setIsTiper(false);
			$historyManager->updateHistory(
				$user,
				'retrait du rôle contributeur',
				$this->getUser()
			);
		} else {
			$user->setIsTiper(true);
			$historyManager->updateHistory(
				$user,
				'rendu contributeur',
				$this->getUser()
			);

			$notification = new Notification();
			$notification->setUser($user);
			$notification->setEntityName('user');
			$notification->setEntityId($id);
			$notification->setColor('red');
			$notification->setIcon('gratipay');
			$notification->setSubject("Vous avez été obtenu le rôle contributeur.");
			$notificationRepository->insert($notification, false);
		}

		$user = $this->repo->update($user);

		return new JsonResponse(
			[
				'message' => "Utilisateur " . $user->getUsername()
					. ($user->getIsTiper() ? " a obtenu" : "a perdu")
					. " le rôle contributeur.",
				'is_tiper'  => $user->getIsTiper()
			],
			Response::HTTP_OK
		);
	}

	/**
	 * @Route("/users/search/{username}", name="search_username", methods={"GET"})
	 * @Route("/users/search/{username}/{limit}", name="search_username_previews", methods={"GET"})
	 */
	public function searchUser($username, $limit = null, Request $request)
	{
		$users = $this->repo->search($username, $limit);

		return $this->json(
			['users' => $users],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	/**
	 * @Route("/reset-password", name="reset_password", methods={"POST"})
	 */
	public function resetPassword(
		Request $request,
		CcMailer $mailer,
		PasswordTokenRenewRepository $passwordTokenRenewRepository
	) {
		try {
			$json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return new JsonResponse(
				['message' => $e->getMessage()],
				Response::HTTP_BAD_REQUEST
			);
		}

		if (!array_key_exists('email', $json)) {
			return new JsonResponse(
				['message' => 'Requête invalide'],
				Response::HTTP_BAD_REQUEST
			);
		}

		$user = $this->repo->findOneBy(["email" => $json["email"]]);

		if (is_null($user))
			return new JsonResponse(
				['message' => 'Utilisateur introuvable'],
				Response::HTTP_NOT_FOUND
			);

		$token = $passwordTokenRenewRepository->createToken($user);

		$email = (new TemplatedEmail())
			->from(new Address("contact@coupcritique.fr", "CoupCritique"))
			->to($user->getEmail())
			->subject("Coup Critique - Renouvellement de mot de passe")
			->htmlTemplate('emails/renew-password.html.twig')
			->context([
				'username' => $user->getUsername(),
				'token' => $token->getToken()
			]);

		$mailer->send($email);

		return new JsonResponse(['message' => "Un mail de renouvellement de mot de passe a bien été envoyé à " . $json["email"] . "."]);
	}

	/**
	 * @Route("/check-renew-password-token/{token}", name="check-token", methods={"GET"}) 
	 */
	public function checkToken(PasswordTokenRenewRepository $password_repo, $token)
	{
		$token = $password_repo->findOneByToken($token);
		if (is_null($token)) {
			return new JsonResponse(
				['messageRenewPassword' => 'Le lien n\'est plus valide.<br /> Veuillez refaire une nouvelle demande de mot de passe.'],
				Response::HTTP_BAD_REQUEST
			);
		} else {
			return new JsonResponse(['messageRenewPassword' => 'token ok'], Response::HTTP_OK);
		}
	}

	/**
	 * @Route("/update-forgotten-password", name="update-forgotten-password", methods={"PUT"}) 
	 */
	public function updateForgottenPassword(Request $request, PasswordTokenRenewRepository $password_repo)
	{
		try {
			$json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e) {
			return new JsonResponse(
				['message' => $e->getMessage()],
				Response::HTTP_BAD_REQUEST
			);
		}

		if (!array_key_exists('renewPasswordToken', $json) || empty($json['new_password'])) {
			return new JsonResponse(
				['message' => 'Requête invalide'],
				Response::HTTP_BAD_REQUEST
			);
		}

		$token = $password_repo->findOneByToken($json['renewPasswordToken']);
		if (!is_null($token)) {
			$this->repo->updatePassword($token->getUser(), $json['new_password']);
			$password_repo->delete($token);

			return new JsonResponse(
				[
					'messageRenewPassword' => 'Mot de passe mis à jour !<br /> Vous pouvez à présent vous connecter.',
					'redirect' => true
				],
				Response::HTTP_OK
			);
		} else {
			//the following line should only be processed if an API client is used instead of the React UI (because that latter invokes checkToken first)
			return new JsonResponse(
				[
					'messageRenewPassword' => 'Le lien n\'est plus valide ou a expiré.<br/>
					Veuillez refaire une demande de mot de passe oublié.'
				],
				Response::HTTP_BAD_REQUEST
			);
		}
	}
}

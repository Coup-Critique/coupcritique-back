<?php

namespace App\Controller\Api;

use App\Repository\PasswordTokenRenewRepository;
use App\Repository\UserRepository;
use App\Service\CcMailer;
use JsonException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class PasswordController extends AbstractController
{
	public function __construct(private readonly UserRepository $repo)
	{
	}

	#[Route(path: '/reset-password', name: 'reset_password', methods: ['POST'])]
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

	#[Route(path: '/check-renew-password-token/{token}', name: 'check-token', methods: ['GET'])]
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

	#[Route(path: '/update-forgotten-password', name: 'update-forgotten-password', methods: ['PUT'])]
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

<?php

namespace App\Controller;

use App\Repository\ActivateUserTokenRepository;
use App\Service\CcMailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class DefaultController extends AbstractController
{
	/**
	 * @Route(
	 *     "/activate-user/renew/{token}",
	 *     name="renew_activate_user_token"
	 * )
	 * @Route(
	 *     "/api/activate-user/renew/{token}/{api_mode}",
	 *     name="api_renew_activate_user_token"
	 * )
	 */
	public function renewActivateUserToken(
		$token,
		?bool $api_mode,
		CcMailer $mailer,
		ActivateUserTokenRepository $activateUserTokenRepository
	) {
		$token = $activateUserTokenRepository->findOneByToken($token);
		if (is_null($token)) {
			$this->addFlash('error', 'Ce lien d\'activation est invalide.');
			return $this->redirectToRoute('home');
		}

		$user = $token->getUser();
		if ($user->getActivated()) {
			$this->addFlash('error', 'Le compte a déjà été activé.');
			return $this->redirectToRoute('home');
		}
		if ($user->getBanned()) {
			$this->addFlash('error', 'Le compte a été bannis.');
			return $this->redirectToRoute('home');
		}

		$token = $token->createToken($token->getUser());

		$em = $this->getDoctrine()->getManager();
		$em->persist($token);
		$em->flush();

		$email = (new TemplatedEmail())
			->from(new Address("contact@coupcritique.fr", "CoupCritique"))
			->to($user->getEmail())
			->subject('Nouveau un lien d\'activation pour votre compte sur coupcritique.fr')
			->htmlTemplate('emails/renew-user-activation.html.twig')
			->context([
				'user'  => $user,
				'token' => $token->getToken()
			]);

		$mailer->send($email);

		if ($api_mode) {
			return $this->json(['message' => 'Le lien précédent a expiré, nouveau lien d\'activation vous a été envoyé par mail']);
		} else {
			$this->addFlash('warning', 'Le lien précédent a expiré, nouveau lien d\'activation vous a été envoyé par mail');

			return $this->redirectToRoute('home');
		}
	}

	/**
	 * @Route(
	 *     "/activate-user/{token}",
	 *     name="activate_user_token"
	 * )
	 */
	public function activateUserToken(
		$token,
		CcMailer $mailer,
		ActivateUserTokenRepository $activateUserTokenRepository
	) {
		$token = $activateUserTokenRepository->findOneByToken($token);
		if (is_null($token)) {
			$this->addFlash('error', 'Ce lien d\'activation est invalide.');
			return $this->redirectToRoute('home');
		}

		$user = $token->getUser();
		if ($user->getActivated()) {
			$this->addFlash('error', 'Le compte a déjà été activé.');
			return $this->redirectToRoute('home');
		}
		if ($user->getBanned()) {
			$this->addFlash('error', 'Le compte a été bannis.');
			return $this->redirectToRoute('home');
		}

		if ($token->isTokenValid()) {
			$user->setActivated(true);

			$email = (new TemplatedEmail())
				->from(new Address("contact@coupcritique.fr", "CoupCritique"))
				->to($user->getEmail())
				->subject('Confirmation de l\'activation de votre compte sur coupritique.fr')
				->htmlTemplate('emails/confirm-user-activation.html.twig')
				->context([
					'user'  => $user,
					'token' => $token->getToken()
				]);

			$mailer->send($email);


			$this->getDoctrine()->getManager()->flush();
			$this->addFlash('success', 'Votre compte a été activé. Vous pouvez désormais vous connecter.');

			return $this->redirectToRoute('home');
		} else {
			return $this->redirectToRoute('renew_activate_user_token', ['token' => $token]);
		}

		return $this->redirectToRoute('home');
	}
}

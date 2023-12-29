<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
	private NotificationRepository $repo;

	public function __construct(NotificationRepository $repo)
	{
		$this->repo = $repo;
	}

	/**
	 * @Route("/notifications", name="notifications", methods={"GET"})
	 */
	public function getUserNotifications()
	{
		return $this->json(
			[
				'notifications' => $this->repo->findByUser($this->getUser()),
				'count' => $this->repo->countByUser($this->getUser())
			],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list']]
		);
	}

	/**
	 * @Route("/notifications/viewed", name="notifications_viewed", methods={"GET"})
	 */
	public function getUserViewedNotifications()
	{
		return $this->json(
			['notifications' => $this->repo->findByUser($this->getUser(), true)],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list']]
		);
	}

	/**
	 * @Route("/notifications/count", name="notifications_count", methods={"GET"})
	 */
	public function countUserNotifications()
	{
		return $this->json(
			['count' => $this->repo->countByUser($this->getUser())],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list']]
		);
	}

	/**
	 * @Route("/notifications/view/{entityName}/{entityId}", name="notifications_view_by_entity", methods={"GET"})
	 */
	public function viewNotificationsByEntity(
		EntityManagerInterface $em,
		string $entityName,
		int $entityId
	) {
		$notifications = $this->repo->findByEntity(
			$this->getUser(),
			$entityName,
			$entityId
		);
		foreach ($notifications as $notification) {
			$notification->setViewed(true);
		}
		$em->flush();
		return $this->json(
			[
				'notifications' => $notifications,
				'count' => $this->repo->countByUser($this->getUser())
			],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list']]
		);
	}

	/**
	 * @Route("/notifications/view/{id}", name="notifications_view_one", methods={"GET"})
	 */
	public function viewOneNotification(EntityManagerInterface $em, int $id)
	{
		$notification = $this->repo->findOneByUser($id, $this->getUser());
		if (is_null($notification)) {
			return new JsonResponse(
				['message' => "Notification introuvable ou déjà marquée comme lue."],
				Response::HTTP_NOT_FOUND
			);
		}
		$notification->setViewed(true);
		$em->flush();
		return $this->json(
			[
				'notification' => $notification,
				'count' => $this->repo->countByUser($this->getUser())
			],
			Response::HTTP_OK,
			[],
			['groups' => ['read:list']]
		);
	}
}

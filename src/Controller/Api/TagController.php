<?php

namespace App\Controller\Api;

use App\Entity\Abstracts\AbstractTag;
use App\Entity\ActualityTag;
use App\Entity\GuideTag;
use App\Entity\Tag;
use App\Entity\VideoTag;
use App\Repository\Abstracts\AbstractTagRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
	/** @var AbstractTagRepository */
	private $repo;

	public function __construct(
		private readonly EntityManagerInterface $em
	) {
	}

	private function setRepository($tags_type)
	{
		$tags_class = [
			'tags' => Tag::class,
			'guide_tags' => GuideTag::class,
			'actuality_tags' => ActualityTag::class,
			'video_tags' => VideoTag::class
		];
		$this->repo = $this->em->getRepository($tags_class[$tags_type]);
	}

	#[Route(path: '/{tags_type}', name: 'tags', methods: ['GET'], requirements: ['tags_type' => 'tags|guide_tags|actuality_tags|video_tags'])]
	public function getTags($tags_type)
	{
		$this->setRepository($tags_type);
		return $this->json(
			['tags' => $this->repo->findBy([], ['sortOrder' => 'ASC'])],
			Response::HTTP_OK,
			[],
			['groups' => 'read:list']
		);
	}

	#[Route(path: '/{tags_type}/{id}', name: 'tag_by_id', methods: ['GET'], requirements: ['tags_type' => 'tags|guide_tags|actuality_tags|video_tags'])]
	public function getTagById($tags_type, $id)
	{
		$this->setRepository($tags_type);
		$tag = $this->repo->find($id);
		if (empty($tag)) {
			return new JsonResponse(['message' => "Mauvais identifiant"], Response::HTTP_NOT_FOUND);
		}

		return $this->json(
			['tag' => $tag],
			Response::HTTP_OK,
			[],
			['groups' => 'read:tag']
		);
	}
}

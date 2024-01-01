<?php

namespace App\Controller\Api;

use App\Entity\Resource;
use App\Normalizer\EntityNormalizer;
use App\Repository\GuideRepository;
use App\Repository\ResourceRepository;
use App\Service\ErrorManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResourceController extends AbstractController implements ContributeControllerInterface
{
    public function __construct(private readonly ResourceRepository $repo)
    {
    }

    #[Route(path: '/resources', name: 'resources', methods: ['GET'])]
    public function getAll()
    {
        $resources = $this->repo->findAll();

        $resourcesByGenAndCat = [0 => []];
        foreach ($resources as $resource) {
            $tier = $resource->getTier();
            $tier = $resource->getTier();
            if(!$resource->getGen() || !$tier || $tier->getShortName() === 'VGC' || $tier->getShortName() === 'BSS'){
                $gen = 0;
            }else{
                $gen = 'Gen ' . $resource->getGen();
            }
            $cat = $resource->getCategory();
            if (!array_key_exists($gen, $resourcesByGenAndCat)) {
                $resourcesByGenAndCat[$gen] = [];
            }
            if (!array_key_exists($cat, $resourcesByGenAndCat[$gen])) {
                $resourcesByGenAndCat[$gen][$cat] = [];
            }
            $resourcesByGenAndCat[$gen][$cat][] = $resource;
        }

        uksort($resourcesByGenAndCat, function($k1, $k2): int{
            if($k1 == '0') return -1;
            if($k2 == '0') return -1;
            return $k1 > $k2 ? -1 : 1;
        });

        return $this->json(
            ['resources' => $resourcesByGenAndCat],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:resource']
        );
    }

    #[IsGranted('ROLE_MODO')]
    #[Route(path: '/resources', name: 'insert_resource', methods: ['POST'])]
    public function insertResource(
        Request $request,
        SerializerInterface $serializer
    ) {
        $json = $request->getContent();
        try {
            /** @var Resource $resource */
            $resource = $serializer->deserialize(
                $json,
                Resource::class,
                'json',
            );

            $this->repo->insert($resource);
        } catch (NotEncodableValueException) {
            // return $this->json(
            //     ['message' => $e->getMessage()],
            //     Response::HTTP_BAD_REQUEST
            // );
        }

        $resource->setCategory(trim($resource->getCategory()));

        return $this->json(
            ['message' => 'Ressource enregistrée', 'resource' => $resource],
            Response::HTTP_OK,
            [],
            ['groups' => 'read:resource']
        );
    }

    #[IsGranted('ROLE_MODO')]
    #[Route(path: '/resources/{id}', name: 'update_resource', methods: ['PUT'])]
    public function updateResource(
        $id,
        Request $request,
        SerializerInterface $serializer
    ) {
        $json = $request->getContent();
        try {
            $resource = $this->repo->find($id);
            $newResource = $serializer->deserialize(
                $json,
                Resource::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $resource,
                    EntityNormalizer::UPDATE_ENTITIES      => [Resource::class]
                ]
            );
            $this->repo->update($newResource);
        } catch (NotEncodableValueException) {
            // return $this->json(
            //     ['message' => $e->getMessage()],
            //     Response::HTTP_BAD_REQUEST
            // );
        }

        $resource->setCategory(trim($resource->getCategory()));

        return new JsonResponse(
            ['message' => "Ressource $id mise à jour", 'id' => $id],
            Response::HTTP_OK
        );
    }

    #[IsGranted('ROLE_MODO')]
    #[Route(path: '/resources/{id}', name: 'delete_resource', methods: ['DELETE'])]
    public function deleteResource($id)
    {
        $resource = $this->repo->find($id);
        if (empty($resource)) {
            return new JsonResponse(
                ['message' => "Mauvais identifiant"],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->repo->delete($resource);

        return new JsonResponse(
            ['message' => "Ressource $id supprimée", 'id' => $id],
            Response::HTTP_OK
        );
    }
}

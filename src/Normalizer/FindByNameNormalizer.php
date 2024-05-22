<?php

namespace App\Normalizer;

use App\Service\GenRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
// use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
// use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @deprecated
 * Entity normalizer
 */
class FindByNameNormalizer implements DenormalizerInterface/* , DenormalizerAwareInterface */
{
    // Cannot use it, makes infinite loop
    // use DenormalizerAwareTrait;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GenRequestManager $genRequestManager,
        protected readonly ObjectNormalizer $objectNormalizer
    ) {
    }

    /** {@inheritdoc} */
    public function getSupportedTypes(?string $format): array
    {
        return ['object' => true];
    }

    /**
     * Use this normalizer as Denormalizer if $data is an array that contain a key 'id'
     * And represents a Entity : $type contains 'App\Entity\'
     */
    /** {@inheritdoc} */
    public function supportsDenormalization(mixed $data, string $class, string $format = null, array $context = []): bool
    {
        return str_starts_with($class, 'App\\Entity\\') && is_string($data);
    }

    /** {@inheritdoc} */
    public function denormalize(mixed $data, string $class, string $format = null, array $context = []): mixed
    {
        if (!empty($data)) {
            $reflectionClass = new \ReflectionClass($class);
            if ($reflectionClass->hasProperty('name')) {
                $repo = $this->em->getRepository($class);
                if ($reflectionClass->hasProperty('gen')) {
                    $entity = $repo->findOneByNameAndGen(
                        $data,
                        $this->genRequestManager->getGenFromRequest()
                    );
                } else {
                    $entity = $repo->findOneByName($data);
                }
                if (!empty($entity)) return $entity;
            }
        }
        return $this->objectNormalizer->denormalize($data, $class, $format, $context);
    }
}

<?php

namespace App\Normalizer;

use App\Service\GenRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Entity normalizer
 */
class FindByNameNormalizer extends ObjectNormalizer
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected GenRequestManager $genRequestManager,
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        PropertyAccessorInterface $propertyAccessor = null,
        PropertyTypeExtractorInterface $propertyTypeExtractor = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        parent::__construct(
            $classMetadataFactory,
            $nameConverter,
            $propertyAccessor,
            $propertyTypeExtractor,
            $classDiscriminatorResolver,
            $objectClassResolver,
            $defaultContext
        );
    }

    /**
     * Use this normalizer as Denormalizer if $data is an array that contain a key 'id'
     * And represents a Entity : $type contains 'App\Entity\'
     */
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, string $format = null): bool
    {
        return str_starts_with($type, 'App\\Entity\\') && is_string($data);
    }

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     * `: mixed` exists in PHP 8
     */
    public function denormalize($data, $class, string $format = null, array $context = [])
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
        // Use normal traitment
        return parent::denormalize($data, $class, $format, $context);
    }
}

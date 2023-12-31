<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Entity normalizer
 */
class EntityNormalizer extends ObjectNormalizer
{
	final public const UPDATE_ENTITIES = 'update_entities';

	public function __construct(
		protected EntityManagerInterface $em,
		?ClassMetadataFactoryInterface $classMetadataFactory = null,
		?NameConverterInterface $nameConverter = null,
		?PropertyAccessorInterface $propertyAccessor = null,
		?PropertyTypeExtractorInterface $propertyTypeExtractor = null,
		?ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
		protected ?callable $objectClassResolver = null,
		array $defaultContext = []
	) {
		$defaultContext = array_merge(
			$defaultContext,
			[self::CIRCULAR_REFERENCE_HANDLER => fn () => null]
		);
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
		return str_starts_with($type, 'App\\Entity\\') && !empty($data['id']);
	}

	/**
	 * {@inheritdoc}
	 */
	public function denormalize($data, $class, string $format = null, array $context = []): mixed
	{
		if (
			array_key_exists(self::UPDATE_ENTITIES, $context)
			&& in_array($class, $context[self::UPDATE_ENTITIES])
		) {
			// Let it to ObjectNormalizer on Update entity
			return parent::denormalize($data, $class, $format, $context);
		}
		// Lazy Loading
		return $this->em->find($class, $data['id']);
		// No Lazy Loading : return $this->em->getRepository($class)->findOneById($data['id']);
	}
}

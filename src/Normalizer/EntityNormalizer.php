<?php

namespace App\Normalizer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EntityNormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
	use DenormalizerAwareTrait;

	final public const UPDATE_ENTITIES = 'update_entities';

	public function __construct(protected readonly EntityManagerInterface $em)
	{
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
		return str_starts_with($class, 'App\\Entity\\') && !empty($data['id']);
	}

	/** {@inheritdoc} */
	public function denormalize(mixed $data, string $class, string $format = null, array $context = []): mixed
	{
		if (
			array_key_exists(self::UPDATE_ENTITIES, $context)
			&& in_array($class, $context[self::UPDATE_ENTITIES])
		) {
			// Let it to ObjectNormalizer on Update entity
			return $this->denormalizer->denormalize($data, $class, $format, $context);
		}
		// Lazy Loading
		return $this->em->find($class, $data['id']);
		// No Lazy Loading : return $this->em->getRepository($class)->findOneById($data['id']);
	}
}

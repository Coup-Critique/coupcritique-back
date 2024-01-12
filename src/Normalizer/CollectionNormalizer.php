<?php

namespace App\Normalizer;


use Doctrine\Common\Collections\Collection;
use LogicException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Class CollectionNormalizer
 * @package App\Normalizer
 */
class CollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** {@inheritdoc} */
    public function supportsDenormalization(mixed $data, string $class, string $format = null, array $context = []): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getSupportedTypes(?string $format): array
    {
        return [Collection::class => true];
    }

    /** {@inheritdoc} */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Collection;
    }

    /** {@inheritdoc} */
    public function normalize(mixed $collection, ?string $format = null, array $context = []): mixed
    {

        $normalized = [];
        $collection = $collection->toArray();
        foreach ($collection as $val) {
            $normalized[] = $this->normalizer->normalize($val, $format, $context);
        }

        return $normalized;
    }
}

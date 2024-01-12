<?php

namespace App\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizer implements NormalizerInterface
{
    /** {@inheritdoc} */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof \DateTime;
    }

    /** {@inheritdoc} */
    public function getSupportedTypes(?string $format): array
    {
        return [\DateTime::class => true];
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): mixed
    {
        if (empty($object) || $object->format('Y') === '-0001') return '';
        return $object->format('Y-m-d');
    }
}

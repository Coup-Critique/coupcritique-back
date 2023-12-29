<?php

namespace App\Normalizer;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class CollectionNormalizer
 * @package App\Normalizer
 */
class CollectionNormalizer extends ObjectNormalizer
{
    /** {@inheritdoc} */
    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Collection;
    }

    /** {@inheritdoc} */
    public function normalize($collection, ?string $format = null, array $context = [])
    {
        $normalized = [];
        $collection = $collection->toArray();
        foreach ($collection as $val) {
            $normalized[] = $this->serializer->normalize($val, $format, $context);
        }

        return $normalized;
    }
}

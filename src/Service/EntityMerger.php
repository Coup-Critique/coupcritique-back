<?php

namespace App\Service;

use Symfony\Component\Serializer\SerializerInterface;

class EntityMerger
{
    public function __construct(protected SerializerInterface $serializer)
    {
    }

    public function merge($data, $mainClass, $context = [], ?callable $makeKey = null)
    {
        $return = [];
        foreach ($data as $datum) {
            if (is_null($datum)) continue;
            $value = $this->serializer->normalize($datum, 'json', $context);
            $class = $datum::class;
            if ($datum instanceof $mainClass) {
                $return[] = $value;
            } else if (count($return) && $class) {
                $key = null;
                if ($makeKey) {
                    $key = $makeKey($datum);
                }
                if (empty($key)) {
                    // Replace App\Entity\
                    $key = lcfirst(substr($class, 11));
                }
                $return[count($return) - 1][$key] = $value;
            }
        }

        return $return;
    }

    public static function makeUsageKey($usageEl, $usageName, $usageClass)
    {
        if (!$usageEl instanceof $usageClass) return null;
        $tier = $usageEl->getTierUsage()->getTier();
        if ($tier->getShortName() === 'VGC') {
            return $usageName . 'Vgc';
        }
        if ($tier->getShortName() === 'BSS') {
            return $usageName . 'Bss';
        }
        return $usageName;
    }
}

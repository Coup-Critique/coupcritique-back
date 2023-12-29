<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class StringCollectionDenormalizer
{

    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function denormalize($entity, string $className, array $jsonArray, $gen)
    {
        $reflectionClass = new \ReflectionClass($className);
        foreach ($jsonArray as $key => $value) {
            // filter to get only keys like str_entities_set 
            if (!is_string($value) || !str_starts_with($key, "str_")) continue;
            if (!strstr($value, "/")) continue;

            $attrName = substr($key, 4);
            // attribute name not found
            if (!$reflectionClass->hasProperty($attrName)) continue;

            list($getter, $adder, $remover) = $this->getClassMethodes($attrName);

            // generated adder name not found
            if (!$reflectionClass->hasMethod($adder)) continue;
            // generated getter name not found
            if (!$reflectionClass->hasMethod($getter)) continue;

            $attrClassName = $this->getClassNameFromAnnotations($reflectionClass, $attrName);
            // class not found in annotations
            if (is_null($attrClassName)) continue;

            $AttrClass = 'App\\Entity\\' . $attrClassName;
            $targetEntityClassName = ltrim($attrClassName, 'PokemonSet');
            $targetEntityClassName = preg_replace('/(One|Two|Three|Four)/', '', $targetEntityClassName);
            if ($targetEntityClassName === 'Tera') {
                $TargetEntityClass = 'App\\Entity\\Type';
            } else {
                $TargetEntityClass = 'App\\Entity\\' . $targetEntityClassName;
            }
            $targetEntityGetter = 'get' . $targetEntityClassName;
            $targetEntitySetter = 'set' . $targetEntityClassName;
            $targetEntityRepository = $this->em->getRepository($TargetEntityClass);

            $oldValueStr = array_reduce(
                $entity->$getter()->toArray(),
                function ($str, $el) use ($targetEntityGetter) {
                    return $str
                        ? $str . "/" . $el->$targetEntityGetter()->getName()
                        : $el->$targetEntityGetter()->getName();
                },
                ''
            );

            $oldValueStr = preg_replace('/\s+/', '', $oldValueStr);
            $noSpaceValue = preg_replace('/\s+/', '', $value);
            // value hasn't been updated
            if ($oldValueStr === $noSpaceValue) continue;
            // remove old values
            foreach ($entity->$getter() as $oldValue) {
                $entity->$remover($oldValue);
            }

            foreach (explode('/', $value) as $i => $elName) {
                $elName = trim($elName);

                if (method_exists($targetEntityRepository, 'findOneByNameAndGen')) {
                    $targetEntity = $targetEntityRepository->findOneByNameAndGen($elName, $gen);
                } else {
                    $targetEntity = $targetEntityRepository->findOneByName($elName);
                }
                // target entity not found
                if (empty($entity)) continue;
                // Add a new set Option from found target entity
                $element = new $AttrClass();
                $element->$targetEntitySetter($targetEntity);
                $element->setRank($i);
                $entity->$adder($element);
            }
        }
    }

    public function getClassNameFromAnnotations(
        \ReflectionClass $reflectionClass,
        string $attrName
    ): ?string {
        // entities_set annotations
        $annotations = explode(
            PHP_EOL,
            $reflectionClass->getProperty($attrName)->getDocComment()
        );
        // scrap annotations to find key entity name
        foreach ($annotations as $annotation) {
            if (preg_match(
                '/targetEntity=([A-Za-z]*)::/',
                $annotation,
                $result
            )) {
                return $result[1];
            }
        }
        return null;
    }

    /**
     * @param string $attrName
     * @return string[$getter, $adder, $remover] 
     */
    public function getClassMethodes($attrName)
    {
        $solo = [];
        $multiple = [];
        foreach (explode('_', $attrName) as $str) {
            $str = ucfirst($str);
            $multiple[] = $str;
            if (str_ends_with($str, 'ies')) {
                $str = rtrim($str, 'ies');
                $str .= "y";
            } else {
                $str = rtrim($str, 's');
            }
            $solo[] = $str;
        }
        $multiple = join('', $multiple);
        $solo = join('', $solo);
        return ["get$multiple", "add$solo", "remove$solo"];
    }
}

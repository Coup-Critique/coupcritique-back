<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class DescriptionParser
{
    final public const PARSABLE = ['Pokemon', 'Item', 'Ability', 'Tier', 'Type', 'Move'];

    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    public function parse(?string $description, $gen): array
    {
        if (is_null($description)) return [];
        $description = htmlspecialchars($description);
        preg_match_all(
            '/\[[^\]^\s]*\:[^\]]*\]/',
            $description,
            $matches
        );
        $return          = ['\n' => '<br />'];
        $reflectionClass = null;
        $repository      = null;
        if (!count($matches) || !count($matches[0])) return $return;
        foreach ($matches[0] as $string) {
            [$className, $value, $displayName] = $this->splitVar($string);
            if (!in_array($className, self::PARSABLE)) continue;
            $fullClassName = "App\\Entity\\$className";
            if (is_null($reflectionClass) || $reflectionClass->getName() !== $fullClassName) {
                $reflectionClass = new \ReflectionClass($fullClassName);
                $repository      = $this->em->getRepository($fullClassName);
            }
            if (is_null($repository) || !$reflectionClass->hasProperty('name')) {
                continue;
            }
            if (method_exists($repository, 'findOneByNameAndGen')) {
                $entity = $repository->findOneByNameAndGen($value, $gen);
            } else {
                $entity = $repository->findOneByName($value);
            }
            if (is_null($entity)) continue;
            $return[$string] = [
                'id'     => $entity->getId(),
                'entity' => $className,
                'name'   => $entity->getName(),
                'nom'    => $entity->getNom(),
                'displayName' => $displayName == '1' ? true : false
            ];
        }
        return $return;
    }

    public function parseToWysiwyg(?string $description, $gen): string
    {
        if (is_null($description)) return [];
        $reflectionClass = null;
        $repository      = null;
        $description = str_replace('\n', '<br />', $description);
        return preg_replace_callback(
            '/\[[^\]^\s]*\:[^\]]*\]/',
            function ($matches) use ($reflectionClass, $repository, $gen) {
                $match = $matches[0];
                [$className, $value, $displayName] = $this->splitVar($match);
                if (!in_array($className, self::PARSABLE)) return $match;
                $fullClassName = "App\\Entity\\$className";
                if (is_null($reflectionClass) || $reflectionClass->getName() !== $fullClassName) {
                    $reflectionClass = new \ReflectionClass($fullClassName);
                    $repository      = $this->em->getRepository($fullClassName);
                }
                if (is_null($repository) || !$reflectionClass->hasProperty('name')) {
                    return $match;
                }
                if (method_exists($repository, 'findOneByNameAndGen')) {
                    $entity = $repository->findOneByNameAndGen($value, $gen);
                } else {
                    $entity = $repository->findOneByName($value);
                }
                if (is_null($entity)) return $match;

                $name = $entity->getNom() ?: $entity->getName();
                switch ($className) {
                    case 'Pokemon':
                        return "<a href='/entity/pokemons/{$entity->getId()}' class='sprite' title='$name' target='_self'>"
                            . ($displayName == '1' ? "<span class='toggle'>$name</span>" : '')
                            . "<img src='/images/pokemons/sprites/" . Utils::formatFileName($entity->getName()) . ".png' alt='Pokémon $name' />"
                            . "<span class='sr-only'>$name</span>"
                            . "</a>";
                    case 'Item':
                        return "<a href='/entity/items/{$entity->getId()}' class='sprite' title='$name' target='_self'>"
                            . ($displayName == '1' ? "<span class='toggle'>$name</span>" : '')
                            . "<img src='/images/items/sprites/" . Utils::formatFileName($entity->getName()) . ".png' alt='Objet $name' />"
                            . "<span class='sr-only'>$name</span>"
                            . "</a>";
                    case 'Ability':
                        return "<a class='ability' href='/entity/abilities/{$entity->getId()}' target='_self'>$name</a>";
                    case 'Move':
                        return "<a class='move' href='/entity/moves/{$entity->getId()}' target='_self'>$name</a>";
                    case 'Type':
                        return "<a href='/entity/types/{$entity->getId()}' class='type' target='_self'>"
                            . "<img src='/images/types/" . Utils::formatFileName($entity->getName()) . ".png' alt='Type $name'/>"
                            . "<span class='sr-only'>$name</span>"
                            . "</a>";
                }
                // reset
                $displayName = null;
            },
            $description
        );
    }

    protected function splitVar(string $string): array
    {
        $split = explode(':', trim($string, '[]'), 3);
        $displayName = $split[2] ?? null;
        return [$split[0], $split[1], $displayName];
    }
}

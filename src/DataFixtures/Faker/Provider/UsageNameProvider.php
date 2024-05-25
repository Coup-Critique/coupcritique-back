<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use Faker\Provider\Base;

class UsageNameProvider extends Base
{
    public static function usageName(string $string): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]/', '', $string));
    }
}
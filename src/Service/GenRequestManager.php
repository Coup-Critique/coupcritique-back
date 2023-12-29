<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GenRequestManager
{
    protected ?Request $request = null;
    public static array $gens = [1, 2, 3, 4, 5, 6, 7, 8, 9];

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getGenFromRequest(): string
    {
        $gen = $this->request->get('gen');
        if (!empty($gen) && in_array($gen, self::$gens)) return $gen;
        return end(self::$gens);
    }

    public static function getLastGen(){
        return end(self::$gens);
    }

    public static function formatAvailableGens(array $queryResult){
        return array_reduce(
            $queryResult,
            function($acc, $res) {
                $acc[$res['gen']] = $res['id'];
                return $acc;
            }, 
            []
        );
    }
}

<?php

namespace App\Service;

use App\Entity\User;

class HistoryManager
{
    public static function updateHistory($entity, $action, User $user): void
    {
        // TODO : dans l'ideal verfier que l'entité a bien setHistory et getHistory (faire une interface ?)
        $nextHistory =  ($entity->getHistory() ?: '')
            . date('d/m/Y')
            . " $action par {$user->getUsername()}.<br/>";

        $nextHistory = self::removeTilBr($nextHistory, 1000);

        $entity->setHistory($nextHistory);
    }

    protected static function removeTilBr(string $str,  int $len): string
    {
        while (strlen($str) > $len) {
            $breakPos = strpos($str, '<br/>');
            if ($breakPos !== false) {
                $str = substr($str, $breakPos + 5); // +5 to also remove the '<br/>'
            } else {
                $str = substr($str, -1000); // keep the last 1000 characters
                break; // exit the loop if '<br/>' is not found
            }
        }
        return $str;
    }
}

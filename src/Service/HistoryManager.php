<?php

namespace App\Service;

use App\Entity\User;

class HistoryManager
{
    public static function updateHistory($entity, $action, User $user)
    {
        // TODO : dans l'ideal verfier que l'entité a bien setHistory et getHistory (faire une interface ?)
        $entity->setHistory(
            ($entity->getHistory() ?: '')
            . date('d/m/Y')
            . " $action par {$user->getUsername()}.<br/>"
        );
    }
}

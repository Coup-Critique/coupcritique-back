<?php

namespace App\Repository\Traits;

trait MaxLengthTrait
{
    protected int $maxLength = 500;

    public function setMaxLength($maxLength): void
    {
        $maxLength = intval($maxLength);
        if ($maxLength > 0 && $maxLength < 500) {
            $this->maxLength = $maxLength;
        }
    }
}

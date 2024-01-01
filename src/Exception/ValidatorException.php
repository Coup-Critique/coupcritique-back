<?php

namespace App\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorException extends \Exception
{
    public function __construct(
        private readonly ConstraintViolationListInterface $constraintListObject,
        int $code = 0,
        Exception $previous = null
    ) {
        if ($constraintListObject->count() > 0) {
            //   $errors = [];
            //     $errors["message"] = [];
            //     foreach($this->constraintListObject as $constraintError)
            //     {
            //         array_unshift($errors["message"],$constraintError->getMessage());
            //     }
            //     $this->message = $errors;
            // }elseif($constraintListObject->count() == 1){
            $this->message = ["message" => $constraintListObject->get(0)->getMessage()];
        }
        parent::__construct(json_encode($this->message), $code, $previous);
    }

    public function getDecodedMessage(): ?array
    {
        return json_decode($this->message, true);
    }
}

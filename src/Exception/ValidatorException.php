<?php

namespace App\Exception;

use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorException extends \Exception
{   
    /**
     * @var ConstraintViolationListInterface $constraintListObject
     */
    private $constraintListObject;

    /**
     * @var string $message
     */
    protected $message;

    public function __construct(ConstraintViolationListInterface $constraintListObject, $code = 0, Exception $previous = null)
    {
        $this->constraintListObject = $constraintListObject;
        if($constraintListObject->count() > 0){
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
        parent::__construct(json_encode($this->message),$code,$previous);
    }

    public function getDecodedMessage(): ?array
    {
        return json_decode($this->message,true);
    }
}
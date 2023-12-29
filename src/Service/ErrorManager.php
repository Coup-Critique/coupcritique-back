<?php

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorManager
{
	/**
	 *  This method is used to transformate the return of symfony validator
	 * @param ConstraintViolationListInterface $violationList
	 * @return array
	 */
	public function parseErrors(ConstraintViolationListInterface $violationList) : array
	{
		$return = [];
		foreach ($violationList as $value) {
			/** @var $value ConstraintViolationInterface */
			$return[] = [
				'field'   => $value->getPropertyPath(),
				'message' => $value->getMessage()
			];
		}
		return $return;
	}
}

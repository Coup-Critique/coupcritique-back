<?php

namespace App\Service;

class WeaknessManager
{
	public static function mergeWeaknesses($weaknesses_type_1, $weaknesses_type_2)
	{
		foreach ($weaknesses_type_2 as $value_2) {
			$found = false;
			foreach ($weaknesses_type_1 as &$value_1) {
				if ($value_2->getTypeAttacker()->getId() === $value_1->getTypeAttacker()->getId()) {
					$value_1->setRatio(
						$value_1->getRatio() * $value_2->getRatio()
					);
					$found = true;
					break;
				}
			}
			if (!$found) {
				$weaknesses_type_1[] = $value_2;
			}
		}

		return $weaknesses_type_1;
	}
}

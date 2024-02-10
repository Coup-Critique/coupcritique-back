<?php

namespace App\Service;

use App\Entity\Interfaces\CalendableInterface;

class CalendarMaker
{
    /** @param CalendableInterface[] $elements */
    public function makeCalendar(array $elements): array
    {
        $return = [];
        $rowDate = [];
        foreach ($elements as $i => $element) {
            $row = 0;
            if ($i > 0) {
                for (; $row < $i;) {
                    if (isset($rowDate[$row]) && $element->getStartDate() < $rowDate[$row]) {
                        $row++;
                    } else {
                        $rowDate[$row] = $element->getEndDate();
                        break;
                    }
                }
            } else {
                $rowDate[0] = $element->getEndDate();
            }
            if (empty($return[$row])) $return[$row] = [];
            $return[$row][] = [
                'start' => intval($element->getStartDate()->format('n')) - 1 + ($this->calcDayMonthPercent($element->getStartDate()) / 100),
                'end' => intval($element->getEndDate()->format('n')) - 1 + ($this->calcDayMonthPercent($element->getEndDate()) / 100),
                'element' => $element,
            ];
        }

        return $return;
    }

    public function calcDayMonthPercent(\DateTimeInterface $date): float
    {
        $day = $date->format('j');
        $days = date('t', $date->getTimestamp());
        return $this->roundTo25(($day / $days) * 100, 2);
    }

    public function roundTo25(float $percent): float
    {
        return round($percent / 25) * 25;
    }
}

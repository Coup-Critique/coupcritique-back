<?php

namespace App\Repository\Traits;

trait OrderTrait
{
    protected ?string $order = null;

    protected string $orderDirection = 'ASC';

    public function getOrder(): ?string
    {
        return $this->order;
    }

    public function setOrder(?string $order = ''): void
    {
        $this->order = $order;
    }

    public function getOrderDirection(): string
    {
        if (empty($this->orderDirection)) {
            $this->setOrderDirection();
        }
        return $this->orderDirection;
    }

    /**
     * @param string $orderDirection
     */
    public function setOrderDirection($orderDirection = null): void
    {
        switch ($orderDirection) {
            case 'descending':
            case 'DESC':
                $this->orderDirection = 'DESC';
                break;
            default:
                $this->orderDirection = 'ASC';
                break;
        }
    }
}

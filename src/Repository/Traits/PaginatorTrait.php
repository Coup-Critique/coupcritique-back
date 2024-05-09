<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

// use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

trait PaginatorTrait
{
    protected int $page = 1;

    protected int $maxResults = 50;

    protected ?bool $withoutPaginate = false;

    protected int $counter = 0;

    protected array $selects = [];
    protected array $parameters = [];

    // protected ?DoctrinePaginator $paginator =null;

    public function paginate(string $alias, QueryBuilder $query, $page)
    {
        // if($this->isWithoutPaginate()) {
        //     return $query->getQuery()->getResult();
        // }
        $firstResult = ($page - 1) * $this->getMaxResults();
        $cloneQuery = clone $query;
        $cloneQuery->select("DISTINCT($alias.id) AS id");
        if (!empty($this->selects)) {
            $cloneQuery->addSelect($this->selects);
        }
        $ids = $cloneQuery->getQuery()
            ->setFirstResult($firstResult)
            ->setMaxResults($this->getMaxResults())
            ->getArrayResult();

        if (empty($ids)) return [];

        $ids = array_map(fn ($res) => $res['id'], $ids);

        $counterQuery = $cloneQuery->select("COUNT(DISTINCT($alias.id)) AS counter");
        if (!empty($this->selects)) {
            $counterQuery->addSelect($this->selects);
        }
        $counter = $counterQuery->orderBy("$alias.id", 'ASC')
            ->getQuery()
            ->getArrayResult();

        $this->counter = isset($counter[0]) ? $counter[0]['counter'] : 0;

        // override where and parameters, keep some parameters because can only remove HIDDEN selects
        $query->setParameters($this->parameters);
        return $query->where("$alias.id IN (" . implode(',', $ids) . ")")
            ->getQuery()
            ->getResult();
    }


    public function getNbPages(): int
    {
        return ceil($this->counter / $this->getMaxResults());
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(int $page = 1): void
    {
        $this->page = $page;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    public function isWithoutPaginate(): bool
    {
        return $this->withoutPaginate;
    }

    public function setWithoutPaginate(bool $withoutPaginate): void
    {
        $this->withoutPaginate = $withoutPaginate;
    }

    public function getSelects(): ?array
    {
        return $this->selects;
    }

    /**
     * @param string|array $select 
     * @return QueryBuilder|null 
     */
    public function addPaginatorSelect($select, ?QueryBuilder $query = null, ?array $parameters = null): ?QueryBuilder
    {
        if (is_array($select)) {
            $this->selects = array_merge($this->selects, $select);
        } else {
            $this->selects[] = $select;
        }
        if ($query) {
            $query->addSelect($select);
        }
        if ($parameters) {
            $this->parameters = $parameters;
        }
        return $query;
    }
}

<?php

namespace App\Algolia\Sonata\ProxyQuery;

use Algolia\SearchBundle\SearchService;
use App\Algolia\Query\QueryBuilder;
use App\Algolia\Query\Result;
use App\Entity\AlgoliaIndexedEntityInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class ProxyQuery implements ProxyQueryInterface
{
    private $algolia;

    /**
     * @var string
     */
    private $class;

    private $sortBy;
    private $sortOrder;

    /**
     * @var int
     */
    private $firstResult;

    /**
     * @var int
     */
    private $maxResults;

    /**
     * @var array
     */
    private $results;
    private $queryBuilder;

    public function __construct(SearchService $algolia, QueryBuilder $queryBuilder, ?string $class = null)
    {
        $this->algolia = $algolia;
        $this->queryBuilder = $queryBuilder;
        $this->class = $class;
    }

    public function __call($name, $args)
    {
        return \call_user_func_array([$this->queryBuilder, $name], $args);
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortOrder($sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getSortOrder(): ?string
    {
        return $this->sortOrder;
    }

    public function setFirstResult($firstResult): self
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    public function getFirstResult(): ?int
    {
        return $this->firstResult;
    }

    public function setMaxResults($maxResults): self
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function execute(array $params = [], $hydrationMode = null)
    {
        if (!is_subclass_of($this->class, AlgoliaIndexedEntityInterface::class)) {
            throw new \InvalidArgumentException('Class is not AlgoliaIndexedEntity');
        }

        return $this->results = Result::create($this->algolia->rawSearch(
            $this->class,
            '',
            [
                'filters' => $this->queryBuilder->getQuery(),
                'hitsPerPage' => $this->maxResults,
                'page' => $this->firstResult ?? 0,
            ]
        ));
    }

    public function setSortBy($parentAssociationMappings, $fieldMapping): self
    {
        return $this;
    }

    public function getSingleScalarResult()
    {
    }

    public function getUniqueParameterId()
    {
        return null;
    }

    public function entityJoin(array $associationMappings)
    {
        return current($associationMappings)['fieldName'];
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}

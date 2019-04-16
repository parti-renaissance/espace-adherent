<?php

namespace AppBundle\Assessor\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AssessorFilters
{
    public const PARAMETER_PAGE = 'page';
    public const PARAMETER_STATUS = 'status';

    private const PER_PAGE = 30;

    private $currentPage;
    private $status;

    final private function __construct()
    {
    }

    public static function fromRequest(Request $request)
    {
        $filters = new static();

        if ($status = $request->query->get(self::PARAMETER_STATUS)) {
            $filters->setStatus($status);
        }

        if ($page = $request->query->getInt(self::PARAMETER_PAGE, 1)) {
            $filters->setCurrentPage($page);
        }

        return $filters;
    }

    public function setCurrentPage(int $page): void
    {
        if ($page < 1) {
            $page = 1;
        }

        $this->currentPage = $page;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    final public function getLimit(): int
    {
        return self::PER_PAGE;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status ?: null;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        $qb
            ->setFirstResult(($this->currentPage - 1) * self::PER_PAGE)
            ->setMaxResults(self::PER_PAGE)
        ;
    }

    final public function toQueryString(): string
    {
        return http_build_query($this->getQueryStringParameters());
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
    }
}

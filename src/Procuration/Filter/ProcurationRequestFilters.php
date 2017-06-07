<?php

namespace AppBundle\Procuration\Filter;

use AppBundle\Entity\Adherent;
use AppBundle\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class ProcurationRequestFilters extends ProcurationFilters
{
    const PARAMETER_STATUS = 'status';

    const PROCESSED = 'processed';
    const UNPROCESSED = 'unprocessed';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNPROCESSED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if ($status && !in_array($status, [self::PROCESSED, self::UNPROCESSED], true)) {
            throw new ProcurationException(sprintf('Unexpected procuration request type "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        if ($this->matchUnprocessedRequests()) {
            $qb
                ->andWhere(sprintf('%s.processed = :flag AND %s.processedAt IS NULL', $alias, $alias))
                ->setParameter('flag', 0)
            ;
        } else {
            $qb
                ->andWhere(sprintf('%s.processed = :flag AND %s.processedAt IS NOT NULL', $alias, $alias))
                ->setParameter('flag', 1)
            ;
        }

        $qb
            ->orderBy(sprintf('%s.processed', $alias), 'ASC')
            ->addOrderBy(sprintf('%s.createdAt', $alias), 'DESC')
            ->addOrderBy(sprintf('%s.lastName', $alias), 'ASC')
        ;
    }

    public function getSQLConditions(Adherent $procurationManager): array
    {
        $sql = [];
        $params = [];
        if ($this->country) {
            $sql[] = 'pr.country = :country';
            $params[':country'] = $this->country;
        }

        if ($this->city) {
            if (is_numeric($this->city)) {
                $sql[] = 'pr.postal_code LIKE :postal_code';
                $params[':postal_code'] = $this->city.'%';
            } else {
                $sql[] = sprintf('LOWER(pr.city) LIKE :city', strtolower($this->city));
                $params[':city'] = '%'.$this->city.'%';
            }
        }

        $codes = [];
        foreach ($procurationManager->getProcurationManagedArea()->getCodes() as $key => $code) {
            if (is_numeric($code)) {
                // Postal code prefix
                $codes[] = 'pr.country = \'FR\'';
                $codes[] = 'pr.postal_code LIKE :m_postal_code_'.$key;
                $params[':m_postal_code_'.$key] = $code.'%';
            } else {
                // Country
                $codes[] = 'pr.country = :m_country_'.$key;
                $params[':m_country_'.$key] = $code;
            }

        }

        if ($codes) {
            $sql[] = '('.implode(' OR ', $codes).')';
        }

        $pagination = sprintf('OFFSET :offset LIMIT :limit');
        $paginationParams = [':offset' => ($this->currentPage - 1) * self::PER_PAGE, ':limit' => self::PER_PAGE];

        if ($sql) {
            return ['WHERE '.implode(' AND ', $sql).' '.$pagination, array_merge($params, $paginationParams)];
        }

        return [$pagination, $paginationParams];
    }

    public function matchUnprocessedRequests(): bool
    {
        return self::UNPROCESSED === $this->getStatus();
    }
}

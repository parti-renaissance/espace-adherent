<?php

declare(strict_types=1);

namespace App\Admin\Exporter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;

class IteratorCallbackDataSource implements DataSourceInterface
{
    public const CALLBACK = 'callback';

    public function createIterator(ProxyQueryInterface $query, array $fields): \Iterator
    {
        if (!isset($fields[self::CALLBACK]) || !\is_callable($fields[self::CALLBACK])) {
            throw new \InvalidArgumentException(self::class.' needs a callback field');
        }

        $rootAlias = current($query->getQueryBuilder()->getRootAliases());

        $query->getQueryBuilder()->distinct();
        $query->getQueryBuilder()->select($rootAlias);

        $sortBy = $query->getSortBy();

        if (null !== $sortBy) {
            $rootAliasSortBy = strstr($sortBy, '.', true);

            if ($rootAliasSortBy !== $rootAlias) {
                $query->getQueryBuilder()->addSelect($rootAliasSortBy);
            }
        }

        $query->setFirstResult(null);
        $query->setMaxResults(null);

        return new IteratorCallbackSourceIterator(
            $query->getQuery()->iterate(),
            $fields[self::CALLBACK]
        );
    }
}

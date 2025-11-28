<?php

declare(strict_types=1);

namespace App\Admin\Exporter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\Exporter\Source\AbstractPropertySourceIterator;

class DataSourceDecorator implements DataSourceInterface
{
    public function __construct(private readonly DataSourceInterface $decorated)
    {
    }

    public function createIterator(ProxyQueryInterface $query, array $fields): \Iterator
    {
        $sourceIterator = $this->decorated->createIterator($query, $fields);

        if ($sourceIterator instanceof AbstractPropertySourceIterator) {
            $sourceIterator->setDateTimeFormat('d/m/Y');
        }

        return $sourceIterator;
    }
}

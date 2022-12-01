<?php

namespace App\Admin\Exporter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Sonata\Exporter\Source\AbstractPropertySourceIterator;
use Sonata\Exporter\Source\SourceIteratorInterface;

class DataSourceDecorator implements DataSourceInterface
{
    public function __construct(private readonly DataSourceInterface $decorated)
    {
    }

    public function createIterator(ProxyQueryInterface $query, array $fields): SourceIteratorInterface
    {
        $sourceIterator = $this->decorated->createIterator($query, $fields);

        if ($sourceIterator instanceof AbstractPropertySourceIterator) {
            $sourceIterator->setDateTimeFormat('d/m/Y');
        }

        return $sourceIterator;
    }
}

<?php

namespace App\Admin\Exporter;

use Sonata\AdminBundle\Exporter\DataSourceInterface;

/**
 * @method setDataSource(DataSourceInterface $dataSource)
 */
trait IterableCallbackDataSourceTrait
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setIterableCallbackDataSource(IteratorCallbackDataSource $callbackDataSource): void
    {
        $this->setDataSource($callbackDataSource);
    }
}

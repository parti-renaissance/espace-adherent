<?php

namespace App\Admin\Exporter;

use Sonata\AdminBundle\Exporter\DataSourceInterface;

/**
 * @method setDataSource(DataSourceInterface $dataSource)
 */
trait IterableCallbackDataSourceTrait
{
    /** @required */
    public function setIterableCallbackDataSource(IteratorCallbackDataSource $callbackDataSource): void
    {
        $this->setDataSource($callbackDataSource);
    }
}

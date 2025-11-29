<?php

declare(strict_types=1);

namespace App\Admin\Exporter;

use Sonata\AdminBundle\Exporter\DataSourceInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @method setDataSource(DataSourceInterface $dataSource)
 */
trait IterableCallbackDataSourceTrait
{
    #[Required]
    public function setIterableCallbackDataSource(IteratorCallbackDataSource $callbackDataSource): void
    {
        $this->setDataSource($callbackDataSource);
    }
}

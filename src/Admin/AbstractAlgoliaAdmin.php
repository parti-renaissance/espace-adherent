<?php

namespace App\Admin;

use App\Algolia\Sonata\Builder\DatagridBuilder;
use App\Algolia\Sonata\Model\ModelManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Contracts\Service\Attribute\Required;

class AbstractAlgoliaAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        unset($sortValues[DatagridInterface::SORT_BY], $sortValues[DatagridInterface::SORT_ORDER]);
    }

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    #[Required]
    final public function setAlgoliaManager(ModelManager $modelManager): void
    {
        parent::setModelManager($modelManager);
    }

    #[Required]
    final public function setAlgoliaDatagridBuilder(DatagridBuilder $datagridBuilder): void
    {
        parent::setDatagridBuilder($datagridBuilder);
    }
}

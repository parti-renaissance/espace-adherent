<?php

namespace App\Admin;

use App\Algolia\Sonata\Builder\DatagridBuilder;
use App\Algolia\Sonata\Model\ModelManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollection;

class AbstractAlgoliaAdmin extends AbstractAdmin
{
    public function getBatchActions()
    {
        return [];
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }

    /** @required */
    final public function setAlgoliaManager(ModelManager $modelManager): void
    {
        parent::setModelManager($modelManager);
    }

    /** @required */
    final public function setAlgoliaDatagridBuilder(DatagridBuilder $datagridBuilder): void
    {
        parent::setDatagridBuilder($datagridBuilder);
    }
}

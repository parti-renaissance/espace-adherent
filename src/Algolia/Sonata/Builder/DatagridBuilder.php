<?php

namespace App\Algolia\Sonata\Builder;

use App\Algolia\Sonata\Pager\Pager;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

class DatagridBuilder implements DatagridBuilderInterface
{
    private $formFactory;
    protected $filterFactory;

    public function __construct(FormFactoryInterface $formFactory, FilterFactoryInterface $filterFactory)
    {
        $this->formFactory = $formFactory;
        $this->filterFactory = $filterFactory;
    }

    public function getBaseDatagrid(AdminInterface $admin, array $values = [])
    {
        $pager = new Pager();

        $formBuilder = $this->formFactory->createNamedBuilder('filter', FormType::class, [], [
            'csrf_protection' => false,
        ]);

        return new Datagrid($admin->createQuery(), $admin->getList(), $pager, $formBuilder, $values);
    }

    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription)
    {
        // set default values
        $fieldDescription->setAdmin($admin);
    }

    public function addFilter(
        DatagridInterface $datagrid,
        $type,
        FieldDescriptionInterface $fieldDescription,
        AdminInterface $admin
    ) {
        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());

        $datagrid->addFilter($filter);
    }
}

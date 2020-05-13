<?php

namespace App\Admin;

use App\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;

class OrganizationalChartItemAdmin extends AbstractAdmin
{
    public $OCItems = [];
    private $organizationalChartItemRepository;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        OrganizationalChartItemRepository $organizationalChartItemRepository
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->organizationalChartItemRepository = $organizationalChartItemRepository;
    }

    protected function configureListFields(ListMapper $list)
    {
        $this->OCItems = $this->organizationalChartItemRepository->getRootNodes();

        $list
            ->add('typeLabel')
            ->addIdentifier('label', null, [
                'label' => 'Fonction',
            ])
            ->add('parent', null, [
                'associated_property' => 'label',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('typeLabel')
            ->add('label', null, [
                'label' => 'Fonction',
            ])
            ->add('parent', null, [
                'associated_property' => 'label',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->add('label', null, [
                'label' => 'Fonction',
            ])
            ->add('parent', ModelListType::class)
        ;
    }
}

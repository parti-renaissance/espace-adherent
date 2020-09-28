<?php

namespace App\Admin\TerritorialCouncil;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TerritorialCouncilAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'id',
    ];

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $query
            ->leftJoin('o.politicalCommittee', 'politicalCommittee')
            ->leftJoin('politicalCommittee.memberships', 'pcMemberships')
            ->leftJoin('o.memberships', 'membership')
            ->addSelect('politicalCommittee', 'pcMemberships', 'membership')
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('id', null, [
                'label' => 'Id',
                'show_filter' => true,
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('codes', null, [
                'label' => 'Codes',
                'show_filter' => true,
            ])
            ->add('referentTags', ModelAutocompleteFilter::class, [
                'label' => 'Referent Tags',
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'name',
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('codes', null, [
                'label' => 'Codes',
            ])
            ->add('membershipsCount', null, [
                'label' => 'Nb membres',
            ])
            ->add('politicalCommitteeMembershipsCount', null, [
                'label' => 'Nb membres au CoPol',
            ])
            ->add('president', null, [
                'label' => 'Président',
                'template' => 'admin/territorial_council/list_president.html.twig',
            ])
            ->add('isActive', null, [
                'label' => 'Actif',
                'editable' => true,
            ])
            ->add('isPoliticalCommitteeActive', 'boolean', [
                'label' => 'CoPol actif',
                'editable' => true,
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
                'template' => 'admin/territorial_council/list_actions.html.twig',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('codes', TextType::class, [
                'label' => 'Codes',
            ])
        ;
    }
}

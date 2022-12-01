<?php

namespace App\Admin\TerritorialCouncil;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TerritorialCouncilAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->leftJoin('o.politicalCommittee', 'politicalCommittee')
            ->leftJoin('politicalCommittee.memberships', 'pcMemberships')
            ->leftJoin('o.memberships', 'membership')
            ->addSelect('politicalCommittee', 'pcMemberships', 'membership')
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
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
            ->add('referentTags', ModelFilter::class, [
                'label' => 'Referent Tags',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'name',
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
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
                'virtual_field' => true,
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
                'template' => 'admin/territorial_council/list_actions.html.twig',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper): void
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

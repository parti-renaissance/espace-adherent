<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CommitteeMergeHistoryAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'date';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function getAccessMapping(): array
    {
        return [
            'merge' => 'MERGE',
            'revert' => 'REVERT',
        ];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->clearExcept('list')
            ->add('merge', 'merge')
            ->add('revert', $this->getRouterIdParameter().'/revert')
        ;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if ('merge' === $action) {
            $actions = parent::configureActionButtons($buttonList, 'show', $object);
        } else {
            $actions = parent::configureActionButtons($buttonList, $action, $object);
        }

        if ($this->hasAccess('merge') && $this->hasRoute('merge')) {
            $actions['merge'] = ['template' => 'admin/committee/merge/merge_button.html.twig'];
        }

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('sourceCommittee', CallbackFilter::class, [
                'label' => 'Comité source',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.sourceCommittee", 'sc')
                        ->andWhere('sc.name LIKE :sourceName')
                        ->setParameter('sourceName', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('destinationCommittee', CallbackFilter::class, [
                'label' => 'Comité de destination',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.destinationCommittee", 'dc')
                        ->andWhere('dc.name LIKE :destinationName')
                        ->setParameter('destinationName', '%'.$value->getValue().'%')
                    ;

                    return true;
                },
            ])
            ->add('date', DateRangeFilter::class, [
                'label' => 'Date de fusion',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('mergedBy', null, [
                'label' => 'Fusionné par',
                'show_filter' => true,
            ])
            ->add('revertedAt', DateRangeFilter::class, [
                'label' => 'Date d\'annulation',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('revertedBy', null, [
                'label' => 'Annulé par',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('sourceCommittee', null, [
                'label' => 'Comité source',
            ])
            ->add('destinationCommittee', null, [
                'label' => 'Comité de destination',
            ])
            ->add('mergedBy', null, [
                'label' => 'Fusionné par',
            ])
            ->add('date', null, [
                'label' => 'Fusionné le',
            ])
            ->add('revertedBy', null, [
                'label' => 'Annulé par',
            ])
            ->add('revertedAt', null, [
                'label' => 'Annulé le',
            ])
        ;

        if ($this->hasAccess('revert') && $this->hasRoute('revert')) {
            $list->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/committee/merge/list_actions.html.twig',
            ]);
        }
    }
}

<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CommitteeMergeHistoryAdmin extends AbstractAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept('list');
    }

    public function configureActionButtons($action, $object = null)
    {
        return [
            'merge' => [
                'template' => 'admin/committee/merge/merge_button.html.twig',
            ],
        ];
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('sourceCommittee', CallbackFilter::class, [
                'label' => 'Comité source',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.sourceCommittee", 'sc')
                        ->andWhere('sc.name LIKE :sourceName')
                        ->setParameter('sourceName', '%'.$value['value'].'%')
                    ;

                    return true;
                },
            ])
            ->add('destinationCommittee', CallbackFilter::class, [
                'label' => 'Comité de destination',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.destinationCommittee", 'dc')
                        ->andWhere('dc.name LIKE :destinationName')
                        ->setParameter('destinationName', '%'.$value['value'].'%')
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
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
        ;
    }
}

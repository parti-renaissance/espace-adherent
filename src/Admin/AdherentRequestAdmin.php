<?php

namespace App\Admin;

use App\Entity\Adherent;
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

class AdherentRequestAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email', CallbackFilter::class, [
                'label' => 'Email',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $uuid = Adherent::createUuid($value->getValue());
                    $qb->andWhere(
                        $qb
                            ->expr()
                            ->orX()
                            ->add("$alias.emailHash = :email_hash")
                            ->add("$alias.email = :email")
                    );
                    $qb->setParameter('email_hash', $uuid->toString());
                    $qb->setParameter('email', $value->getValue());

                    return true;
                },
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
            ->add('utmSource', null, [
                'label' => 'UTM Source',
                'show_filter' => true,
            ])
            ->add('utmCampaign', null, [
                'label' => 'UTM Campagne',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('email', null, [
                'label' => 'Email',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('utmSource', null, [
                'label' => 'UTM Source',
            ])
            ->add('utmCampaign', null, [
                'label' => 'UTM Campagne',
            ])
            ->add('adherent', null, [
                'label' => 'Compte',
            ])
        ;
    }
}

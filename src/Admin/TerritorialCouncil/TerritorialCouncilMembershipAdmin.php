<?php

namespace App\Admin\TerritorialCouncil;

use App\Form\TerritorialCouncil\PoliticalCommitteeQualityChoiceType;
use App\Form\TerritorialCouncil\TerritorialCouncilQualityChoiceType;
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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TerritorialCouncilMembershipAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('territorialCouncil', null, [
                'show_filter' => true,
                'label' => 'Conseil territorial',
            ])
            ->add('qualities', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Qualité',
                'field_type' => TerritorialCouncilQualityChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->leftJoin("$alias.qualities", 'quality')
                        ->andWhere('quality.name IN (:names)')
                        ->setParameter('names', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('pcQualities', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Qualité au CoPol',
                'field_type' => PoliticalCommitteeQualityChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    if (!\in_array('adherent', $qb->getAllAliases())) {
                        $qb
                            ->leftJoin("$alias.adherent", 'adherent')
                            ->leftJoin('adherent.politicalCommitteeMembership', 'pcMembership')
                        ;
                    }
                    $qb
                        ->leftJoin('pcMembership.qualities', 'pcQuality')
                        ->andWhere('pcQuality.name IN (:names)')
                        ->setParameter('names', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('adherent.firstName', null, [
                'show_filter' => true,
                'label' => 'Prénom',
            ])
            ->add('adherent.lastName', null, [
                'show_filter' => true,
                'label' => 'Nom',
            ])
            ->add('joinedAt', DateRangeFilter::class, [
                'show_filter' => true,
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('isInPoliticalCommittee', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Est dans le CoPol ?',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'common.all' => null,
                        'global.yes' => true,
                        'global.no' => false,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    if (!\in_array('adherent', $qb->getAllAliases())) {
                        $qb
                            ->leftJoin("$alias.adherent", 'adherent')
                            ->leftJoin('adherent.politicalCommitteeMembership', 'pcMembership')
                        ;
                    }

                    $condition = true === $value->getValue() ? 'NOT' : '';
                    $qb->andWhere("pcMembership.id IS $condition NULL");

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('adherent', null, [
                'label' => 'Membre',
                'template' => 'admin/territorial_council/list_membership_member.html.twig',
            ])
            ->add('qualities', null, [
                'label' => 'Qualités',
                'template' => 'admin/territorial_council/list_membership_qualities.html.twig',
            ])
            ->add('pcqualities', null, [
                'virtual_field' => true,
                'label' => 'Qualités au CoPol',
                'template' => 'admin/territorial_council/list_membership_political_committee_qualities.html.twig',
            ])
            ->add('joinedAt', null, [
                'label' => 'Date',
                'format' => 'd/m/Y',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/territorial_council/list_membership_actions.html.twig',
            ])
        ;
    }
}

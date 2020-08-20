<?php

namespace App\Admin\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TerritorialCouncilMembershipAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'id',
    ];

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list']);
    }

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'admin/territorial_council/list.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('territorialCouncil', null, [
                'label' => 'Conseil territorial',
            ])
            ->add('qualities', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Qualité',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => TerritorialCouncilQualityEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "territorial_council.membership.quality.$choice";
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb
                        ->leftJoin("$alias.qualities", 'quality')
                        ->andWhere('quality.name IN (:names)')
                        ->setParameter('names', $value['value'])
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
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('adherent', null, [
                'label' => 'Membre',
                'template' => 'admin/territorial_council/list_membership_member.html.twig',
            ])
            ->add('qualities', null, [
                'label' => 'Qualités',
                'template' => 'admin/territorial_council/list_membership_qualities.html.twig',
            ])
            ->add('joinedAt', null, [
                'label' => 'Date',
                'format' => 'd/m/Y',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/territorial_council/list_actions.html.twig',
            ])
        ;
    }
}

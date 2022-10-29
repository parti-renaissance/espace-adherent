<?php

namespace App\Admin\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilMembershipLog;
use App\Form\TerritorialCouncil\TerritorialCouncilQualityChoiceType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TerritorialCouncilMembershipLogAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('adherent', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Adhérent',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'firstName',
                        'lastName',
                        'emailAddress',
                    ],
                ],
            ])
            ->add('isResolved', null, [
                'show_filter' => true,
                'label' => 'Résolu',
            ])
            ->add('qualityName', null, [
                'show_filter' => true,
                'label' => 'Qualité',
                'field_type' => TerritorialCouncilQualityChoiceType::class,
            ])
            ->add('type', null, [
                'show_filter' => true,
                'label' => 'Type',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => TerritorialCouncilMembershipLog::ALL_TYPES,
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
            ])
            ->add('description', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Description',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(sprintf('%s.description LIKE :text', $alias));
                    $qb->setParameter('text', '%'.strtolower($value->getValue()).'%');

                    return true;
                },
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/territorial_council/list_type.html.twig',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('adherent', null, [
                'label' => 'Adhérent',
                'template' => 'admin/territorial_council/list_adherent.html.twig',
            ])
            ->add('qualityName', null, [
                'label' => 'Qualité',
                'template' => 'admin/territorial_council/list_quality_name.html.twig',
            ])
            ->add('actualTerritorialCouncil', null, [
                'label' => 'CoTerr actuel',
            ])
            ->add('actualQualityNames', null, [
                'label' => 'Qualités actuelles',
                'template' => 'admin/territorial_council/list_qualities.html.twig',
            ])
            ->add('foundTerritorialCouncils', null, [
                'label' => 'CoTerr trouvé(s)',
                'template' => 'admin/territorial_council/list_territorial_councils.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('isResolved', null, [
                'label' => 'Résolu',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/territorial_council/membership_log_list_actions.html.twig',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('isResolved', ChoiceType::class, [
                'required' => true,
                'label' => 'Résolu',
                'choices' => [
                    'global.yes' => true,
                    'global.no' => false,
                ],
            ])
        ;
    }
}

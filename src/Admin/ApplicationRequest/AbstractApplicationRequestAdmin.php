<?php

namespace App\Admin\ApplicationRequest;

use App\Form\GenderType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

abstract class AbstractApplicationRequestAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('gender', ChoiceFilter::class, [
                'label' => 'Genre',
                'show_filter' => true,
                'field_type' => GenderType::class,
                'field_options' => [
                    'placeholder' => '',
                ],
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse email',
                'show_filter' => true,
            ])
            ->add('favoriteCities', CallbackFilter::class, [
                'label' => 'Ville choisie (code INSEE)',
                'show_filter' => true,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere("FIND_IN_SET(:inseeCode, $alias.favoriteCities) > 0");
                    $qb->setParameter('inseeCode', $value->getValue());

                    return true;
                },
            ])
            ->add('isAdherent', CallbackFilter::class, [
                'label' => 'Est adhérent ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere("$alias.adherent IS NOT NULL");

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere("$alias.adherent IS NULL");

                            break;
                    }

                    return true;
                },
            ])
            ->add('displayed', BooleanFilter::class, [
                'label' => 'Est affiché ?',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de candidature',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('gender', 'trans', [
                'label' => 'Genre',
                'format' => 'common.gender.%s',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Email',
            ])
            ->add('favoriteCities', null, [
                'label' => 'Ville(s) choisie(s)',
                'template' => 'admin/application_request/show_favorite_cities.html.twig',
            ])
            ->add('isAdherent', 'boolean', [
                'label' => 'Adhérent',
            ])
            ->add('displayed', 'boolean', [
                'label' => 'Affiché',
                'editable' => true,
            ])
            ->add('createdAt', null, [
                'label' => 'Candidaté le',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations personnelles')
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Email',
                ])
                ->add('address', null, [
                    'label' => 'Adresse',
                ])
                ->add('postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('city', null, [
                    'label' => 'Code INSEE',
                ])
                ->add('cityName', null, [
                    'label' => 'Ville',
                ])
                ->add('country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                ])
                ->add('displayed', CheckboxType::class, [
                    'label' => 'Affiché',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }

    protected function configureExportFields(): array
    {
        return [
            'UUID' => 'uuid',
            'Genre' => 'gender',
            'Prénom' => 'firstName',
            'Nom' => 'lastName',
            'Email' => 'emailAddress',
            'Ville(s) demandée(s)' => 'getFavoriteCitiesAsString',
            'Téléphone' => 'phone',
            'Adresse' => 'address',
            'Code postal' => 'postalCode',
            'Ville' => 'cityName',
            'Pays' => 'country',
            'Est adhérent ?' => 'isAdherent',
            'Est affiché ?' => 'displayed',
        ];
    }
}

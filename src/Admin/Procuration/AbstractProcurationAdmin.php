<?php

namespace App\Admin\Procuration;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Geo\Zone;
use App\Form\GenderType;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractProcurationAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit']);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('firstNames', TextType::class, [
                    'label' => 'Prénoms',
                ])
                ->add('lastName', TextType::class, [
                    'label' => 'Nom',
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                ])
                ->add('birthdate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                ])
                ->add('email', null, [
                    'label' => 'Adresse email',
                ])
                ->add('phone', PhoneNumberType::class, [
                    'label' => 'Téléphone',
                    'widget' => PhoneNumberType::WIDGET_COUNTRY_CHOICE,
                    'required' => false,
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-6'])
                ->add('postAddress.address', TextType::class, ['label' => 'Rue'])
                ->add('postAddress.additionalAddress', TextType::class, ['label' => 'Complément d\'adresse', 'required' => false])
                ->add('postAddress.postalCode', TextType::class, ['label' => 'Code postal'])
                ->add('postAddress.cityName', TextType::class, ['label' => 'Ville'])
                ->add('postAddress.country', CountryType::class, ['label' => 'Pays'])
            ->end()
            ->with('Vote', ['class' => 'col-md-6'])
                ->add('round', null, [
                    'label' => 'Tour concerné',
                ])
                ->add('distantVotePlace', null, [
                    'label' => 'Vote là ou il vit',
                    'required' => false,
                ])
                ->add('voteZone', ModelAutocompleteType::class, [
                    'label' => 'Zone de vote',
                    'btn_add' => false,
                    'property' => ['name', 'code', 'postalCode'],
                    'callback' => [$this, 'prepareVoteZoneAutocompleteFilterCallback'],
                ])
                ->add('votePlace', ModelAutocompleteType::class, [
                    'label' => 'Bureau de vote',
                    'required' => false,
                    'btn_add' => false,
                    'property' => ['name', 'code'],
                    'callback' => [$this, 'prepareVotePlaceAutocompleteFilterCallback'],
                ])
                ->add('customVotePlace', null, [
                    'label' => 'Bureau de vote (custom)',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('_fullName', null, [
                'label' => 'Nom',
                'virtual_field' => true,
                'template' => 'admin/procuration_v2/_list_full_name.html.twig',
            ])
            ->add('email', null, [
                'label' => 'Addresse email',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/procuration_v2/_list_phone.html.twig',
            ])
            ->add('voteZone', null, [
                'label' => 'Lieu de vote',
                'template' => 'admin/procuration_v2/_list_vote_zone.html.twig',
            ])
            ->add('votePlace', null, [
                'label' => 'Bureau de vote',
                'template' => 'admin/procuration_v2/_list_vote_place.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('firstNames', null, [
                'label' => 'Prénoms',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
            ])
            ->add('voteZone', ZoneAutocompleteFilter::class, [
                'label' => 'Zone de vote',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 2,
                    'items_per_page' => 20,
                    'property' => ['name', 'code', 'postalCode'],
                    'callback' => [$this, 'prepareVoteZoneAutocompleteFilterCallback'],
                ],
            ])
            ->add('votePlace', ZoneAutocompleteFilter::class, [
                'label' => 'Bureau de vote',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 2,
                    'items_per_page' => 20,
                    'property' => ['name', 'code'],
                    'callback' => [$this, 'prepareVotePlaceAutocompleteFilterCallback'],
                ],
            ])
            ->add('customVotePlace', null, [
                'label' => 'Bureau de vote (custom)',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    public static function prepareVoteZoneAutocompleteFilterCallback(
        AbstractAdmin $admin,
        array $properties,
        string $value
    ): void {
        self::prepareZoneAutocompleteFilterCallback($admin, $properties, $value, [
            Zone::COUNTRY,
            Zone::CITY,
            Zone::BOROUGH,
        ]);
    }

    public static function prepareVotePlaceAutocompleteFilterCallback(
        AbstractAdmin $admin,
        array $properties,
        string $value
    ): void {
        self::prepareZoneAutocompleteFilterCallback($admin, $properties, $value, [Zone::VOTE_PLACE]);
    }

    private static function prepareZoneAutocompleteFilterCallback(
        AbstractAdmin $admin,
        array $properties,
        string $value,
        array $types
    ): void {
        $datagrid = $admin->getDatagrid();
        $qb = $datagrid->getQuery();
        $alias = $qb->getRootAlias();

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }

        $qb
            ->orWhere($orx)
            ->andWhere("$alias.type IN (:zone_types)")
            ->andWhere("$alias.active = 1")
            ->setParameter('zone_types', $types)
        ;
    }
}

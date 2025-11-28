<?php

declare(strict_types=1);

namespace App\Admin\Procuration;

use App\Address\AddressInterface;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\AbstractProcuration;
use App\Entity\ProcurationV2\Round;
use App\Form\GenderType;
use App\Form\ReCountryType;
use App\Form\TelNumberType;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Utils\PhoneNumberUtils;
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
use Sonata\Form\Type\DatePickerType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractProcurationAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit', 'export']);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['validation_groups'] = ['Default', 'procuration:write'];
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
                    'label' => 'Civilité',
                ])
                ->add('birthdate', DatePickerType::class, [
                    'label' => 'Date de naissance',
                ])
                ->add('email', null, [
                    'label' => 'Adresse email',
                ])
                ->add('phone', TelNumberType::class, [
                    'required' => false,
                ])
                ->add('postAddress.address', TextType::class, ['label' => 'Rue'])
                ->add('postAddress.additionalAddress', TextType::class, ['label' => 'Complément d\'adresse', 'required' => false])
                ->add('postAddress.postalCode', TextType::class, ['label' => 'Code postal'])
                ->add('postAddress.cityName', TextType::class, ['label' => 'Ville'])
                ->add('postAddress.country', ReCountryType::class)
                ->add('joinNewsletter', CheckboxType::class, [
                    'label' => 'Inscription à la newsletter',
                    'required' => false,
                    'disabled' => true,
                ])
            ->end()
            ->with('Vote', ['class' => 'col-md-6'])
                ->add('rounds', null, [
                    'label' => 'Tours concernés',
                    'disabled' => true,
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
            ->add('rounds', null, [
                'label' => 'Tours',
                'template' => 'admin/procuration_v2/_list_rounds.html.twig',
            ])
            ->add('_fullName', null, [
                'label' => 'Identité',
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
            ->add('adherent', null, [
                'label' => 'Adhérent',
            ])
            ->add('voteZone', null, [
                'label' => 'Lieu de vote',
                'template' => 'admin/procuration_v2/_list_vote_zone.html.twig',
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
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ["$alias.firstNames", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstNames"],
                            ["$alias.email", "$alias.email"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                        ]
                    );

                    return true;
                },
            ])
            ->add('id', null, [
                'label' => 'ID',
                'show_filter' => false,
            ])
            ->add('rounds', null, [
                'label' => 'Tours concernés',
                'field_options' => [
                    'multiple' => true,
                ],
            ])
            ->add('firstNames', null, [
                'label' => 'Prénoms',
                'show_filter' => false,
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => false,
            ])
            ->add('email', null, [
                'label' => 'Adresse email',
                'show_filter' => false,
            ])
            ->add('isFDE', CallbackFilter::class, [
                'label' => 'FDE',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => static function (string $choice): string {
                        return "global.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->innerJoin("$alias.voteZone", '_fde_vote_zone');

                    switch ($value->getValue()) {
                        case 'yes':
                            $qb
                                ->andWhere('_fde_vote_zone.type = :fde_type_country')
                                ->setParameter('fde_type_country', Zone::COUNTRY)
                                ->andWhere('_fde_vote_zone.code != :fde_code_france')
                                ->setParameter('fde_code_france', AddressInterface::FRANCE)
                            ;

                            return true;

                        case 'no':
                            $qb
                                ->andWhere(
                                    $qb
                                        ->expr()
                                        ->orX()
                                        ->add(
                                            $qb
                                                ->expr()
                                                ->andX()
                                                ->add('_fde_vote_zone.type = :fde_type_country')
                                                ->add('_fde_vote_zone.code = :fde_code_france')
                                        )
                                        ->add('_fde_vote_zone.type != :fde_type_country')
                                )
                                ->setParameter('fde_type_country', Zone::COUNTRY)
                                ->setParameter('fde_code_france', AddressInterface::FRANCE)
                            ;

                            return true;
                        default:
                            return false;
                    }
                },
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
                'show_filter' => false,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    public static function prepareVoteZoneAutocompleteFilterCallback(
        AbstractAdmin $admin,
        array $properties,
        string $value,
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
        string $value,
    ): void {
        self::prepareZoneAutocompleteFilterCallback($admin, $properties, $value, [Zone::VOTE_PLACE]);
    }

    private static function prepareZoneAutocompleteFilterCallback(
        AbstractAdmin $admin,
        array $properties,
        string $value,
        array $types,
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

    protected static function getExportCommonFields(AbstractProcuration $procuration, TranslatorInterface $translator): array
    {
        $adherent = $procuration->adherent;

        return [
            'ID' => $procuration->getId(),
            'UUID' => $procuration->getUuid()->toString(),
            'Élections' => $procuration->rounds->first()->election->name,
            'Tours' => implode(', ', array_map(function (Round $round) {
                return \sprintf('%s (%s)', $round->name, $round->date->format('d/m/Y'));
            }, $procuration->rounds->toArray())),
            'Civilité' => $translator->trans('common.civility.'.$procuration->gender),
            'Nom' => $procuration->lastName,
            'Prénom(s)' => $procuration->firstNames,
            'Adresse email' => $procuration->email,
            'Date de naissance' => $procuration->birthdate->format('d/m/Y'),
            'Téléphone' => PhoneNumberUtils::format($procuration->phone),
            'Addresse' => $procuration->getAddress(),
            'Code postal' => $procuration->getPostalCode(),
            'Ville' => $procuration->getCityName(),
            'Pays' => $procuration->getCountry(),
            'Adhérent' => $adherent instanceof Adherent ? 'oui' : 'non',
            'Téléphone adhérent' => PhoneNumberUtils::format($adherent?->getPhone()),
            'Lieu de vote' => (string) $procuration->voteZone,
            'Bureau de vote' => $procuration->getVotePlaceName(),
            'Créé le' => $procuration->getCreatedAt()->format('Y/m/d H:i:s'),
        ];
    }

    protected static function getExportErrorFields(
        AbstractProcuration $procuration,
    ): array {
        return [
            'ID' => $procuration->getId(),
            'UUID' => $procuration->getUuid()->toString(),
            'Adresse email' => $procuration->email,
            'Nom' => $procuration->lastName,
            'Prénom(s)' => $procuration->firstNames,
            'Date de naissance' => $procuration->birthdate->format('d/m/Y'),
            'Lieu de vote' => (string) $procuration->voteZone,
            'Bureau de vote' => $procuration->getVotePlaceName(),
        ];
    }
}

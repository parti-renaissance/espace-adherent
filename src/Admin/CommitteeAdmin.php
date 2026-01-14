<?php

declare(strict_types=1);

namespace App\Admin;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Committee\Event\BeforeEditCommitteeEvent;
use App\Committee\Event\EditCommitteeEvent;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Form\Admin\AdherentAutocompleteType;
use App\History\AdministratorActionEvents;
use App\History\AdministratorCommitteeActionEvent;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeAdmin extends AbstractAdmin implements ZoneableAdminInterface
{
    use IterableCallbackDataSourceTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TagTranslator $tagTranslator,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Security $security,
    ) {
        parent::__construct();
    }

    /** @param QueryBuilder|ProxyQueryInterface $query */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect('_animator', '_created_by_adherent', '_updated_by_adherent')
            ->leftJoin($alias.'.animator', '_animator')
            ->leftJoin($alias.'.createdByAdherent', '_created_by_adherent')
            ->leftJoin($alias.'.updatedByAdherent', '_updated_by_adherent')
        ;

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function getAccessMapping(): array
    {
        return [
            'approve' => 'APPROVE',
        ];
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);

        if ('list' === $action) {
            $actions['map'] = ['template' => 'admin/committee/action_button_map.html.twig'];
        }

        return $actions;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
            ->add('approve', $this->getRouterIdParameter().'/approve')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Comité', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('phone', null, [
                    'label' => 'Téléphone',
                    'template' => 'admin/adherent/show_phone.html.twig',
                ])
                ->add('facebookPageUrl', null, [
                    'label' => 'Facebook',
                ])
                ->add('twitterNickname', null, [
                    'label' => 'Twitter',
                ])
                ->add('status', null, [
                    'label' => 'Statut',
                ])
                ->add('createdAt', null, [
                    'label' => 'Date de création',
                ])
                ->add('approvedAt', null, [
                    'label' => 'Date d\'approbation',
                ])
                ->add('refusedAt', null, [
                    'label' => 'Date de refus',
                ])
                ->add('closedAt', null, [
                    'label' => 'Date de fermeture',
                ])
            ->end()
        ;
    }

    /**
     * @param Committee $object
     */
    protected function alterObject(object $object): void
    {
        $this->dispatcher->dispatch(new BeforeEditCommitteeEvent($object));

        $this->dispatcher->dispatch(
            new AdministratorCommitteeActionEvent($this->getAdministrator(), $object),
            AdministratorActionEvents::ADMIN_COMMITTEE_BEFORE_UPDATE
        );
    }

    /**
     * @param Committee $object
     */
    protected function postUpdate(object $object): void
    {
        $this->dispatcher->dispatch(new EditCommitteeEvent($object));

        $this->dispatcher->dispatch(
            new AdministratorCommitteeActionEvent($this->getAdministrator(), $object),
            AdministratorActionEvents::ADMIN_COMMITTEE_AFTER_UPDATE
        );
    }

    /**
     * @param Committee $object
     */
    protected function postPersist(object $object): void
    {
        $this->dispatcher->dispatch(
            new AdministratorCommitteeActionEvent($this->getAdministrator(), $object),
            AdministratorActionEvents::ADMIN_COMMITTEE_CREATE
        );
    }

    /**
     * @param Committee $object
     */
    protected function postRemove(object $object): void
    {
        $this->dispatcher->dispatch(
            new AdministratorCommitteeActionEvent($this->getAdministrator(), $object),
            AdministratorActionEvents::ADMIN_COMMITTEE_DELETE
        );
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Général')
                ->with('Comité', ['class' => 'col-md-7'])
                    ->add('name', null, [
                        'label' => 'Nom',
                    ])
                    ->add('description', null, [
                        'label' => 'Description',
                        'attr' => [
                            'rows' => '3',
                        ],
                    ])
                    ->add('slug', null, [
                        'label' => 'Slug',
                    ])
                    ->add('facebookPageUrl', UrlType::class, [
                        'label' => 'Facebook',
                        'required' => false,
                    ])
                    ->add('twitterNickname', null, [
                        'label' => 'Twitter',
                        'required' => false,
                    ])
                ->end()
                ->with('Responsable comité local', ['class' => 'col-md-5'])
                    ->add('animator', AdherentAutocompleteType::class, [
                        'label' => false,
                        'required' => false,
                    ])
                ->end()
            ->end()
            ->tab('Périmètres géographiques')
                ->with('Zones')
                    ->add('zones', ModelAutocompleteType::class, [
                        'label' => false,
                        'multiple' => true,
                        'required' => false,
                        'minimum_input_length' => 1,
                        'items_per_page' => 20,
                        'btn_add' => false,
                        'safe_label' => true,
                        'callback' => [$this, 'prepareAutocompleteFilterCallback'],
                        'to_string_callback' => [$this, 'toStringCallback'],
                        'property' => ['name', 'code'],
                    ])
                ->end()
            ->end()
        ;
    }

    public static function prepareAutocompleteFilterCallback(AbstractAdmin $admin, array $properties, string $value): void
    {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }

        $subQuery = $admin->getModelManager()->getEntityManager(Committee::class)->createQueryBuilder()
            ->select('z.id')
            ->from(Committee::class, 'c')
            ->join('c.zones', 'z')
            ->getDQL()
        ;

        $qb
            ->andWhere(\sprintf('%s.id NOT IN (%s)', $alias, $subQuery))
            ->andWhere(\sprintf('%s.active = 1', $alias))
            ->andWhere($orx)
        ;
    }

    public function toStringCallback(Zone $zone): string
    {
        return \sprintf(
            '%s (%s) <span class="badge bg-gray-active">%s</span>',
            $zone->getName(),
            $zone->getCode(),
            $this->getTranslator()->trans('geo_zone.'.$zone->getType()),
        );
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('animator', CallbackFilter::class, [
                'label' => 'Responsable',
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
                            ['_animator.firstName', '_animator.lastName'],
                            ['_animator.lastName', '_animator.firstName'],
                            ['_animator.emailAddress', '_animator.emailAddress'],
                        ],
                        [
                            '_animator.phone',
                        ],
                        [
                            '_animator.id',
                            '_animator.uuid',
                            '_animator.publicId',
                        ]
                    );

                    return true;
                },
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'label' => 'Périmètres géographiques',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'multiple' => true,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'name',
                        'code',
                    ],
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('updatedAt', DateRangeFilter::class, [
                'label' => 'Date de dernière modification',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('_created_by_adherent', CallbackFilter::class, [
                'label' => 'Créé par',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ['_created_by_adherent.firstName', '_created_by_adherent.lastName'],
                            ['_created_by_adherent.lastName', '_created_by_adherent.firstName'],
                            ['_created_by_adherent.emailAddress', '_created_by_adherent.emailAddress'],
                        ],
                        [
                            '_created_by_adherent.phone',
                        ],
                        [
                            '_created_by_adherent.id',
                            '_created_by_adherent.uuid',
                            '_created_by_adherent.publicId',
                        ]
                    );

                    return true;
                },
            ])
            ->add('_updated_by_adherent', CallbackFilter::class, [
                'label' => 'Modifié par',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ['_updated_by_adherent.firstName', '_updated_by_adherent.lastName'],
                            ['_updated_by_adherent.lastName', '_updated_by_adherent.firstName'],
                            ['_updated_by_adherent.emailAddress', '_updated_by_adherent.emailAddress'],
                        ],
                        [
                            '_updated_by_adherent.phone',
                        ],
                        [
                            '_updated_by_adherent.id',
                            '_updated_by_adherent.uuid',
                            '_updated_by_adherent.publicId',
                        ]
                    );

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('assembly', null, [
                'label' => 'Assemblée',
                'virtual_field' => true,
                'template' => 'admin/committee/list_assembly.html.twig',
                'header_style' => 'min-width: 200px',
            ])
            ->addIdentifier('name', null, [
                'label' => 'Nom',
                'header_style' => 'min-width: 200px',
            ])
            ->add('animator', null, [
                'label' => 'Responsable',
                'template' => 'admin/committee/list_animator.html.twig',
                'header_style' => 'min-width: 280px',
            ])
            ->add('members', null, [
                'label' => 'Membres',
                'virtual_field' => true,
                'template' => 'admin/committee/list_members.html.twig',
                'header_style' => 'min-width: 145px',
            ])
            ->add('elections', null, [
                'label' => 'Elections',
                'virtual_field' => true,
                'template' => 'admin/committee/list_elections.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/committee/list_actions.html.twig',
            ])
            ->add('zones', null, [
                'label' => 'Zones',
                'template' => 'admin/committee/list_zones.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le',
            ])
            ->add('updatedAt', null, [
                'label' => 'Modifié le',
            ])
            ->add('authors', null, [
                'label' => 'Auteurs',
                'virtual_field' => true,
                'template' => 'admin/committee/list_authors.html.twig',
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [IteratorCallbackDataSource::CALLBACK => function (array $committee) {
            /** @var Committee $committee */
            $committee = $committee[0];

            $animator = $committee->animator;

            try {
                return [
                    'Région' => implode(', ', array_map(function (Zone $zone): string {
                        return \sprintf('%s (%s)', $zone->getName(), $zone->getCode());
                    }, $committee->getParentZonesOfType(Zone::REGION))),
                    'Assemblée' => ($assemblyZone = $committee->getAssemblyZone())
                        ? \sprintf('%s (%s)', $assemblyZone->getName(), $assemblyZone->getCode())
                        : null,
                    'Nom' => $committee->getName(),
                    'Description' => $committee->getDescription(),
                    'Zones' => implode(', ', array_map(function (Zone $zone): string {
                        return \sprintf('%s (%s)', $zone->getName(), $zone->getCode());
                    }, $committee->getZones()->toArray())),
                    'Numéro adhérent RCL' => $animator?->getPublicId(),
                    'Nom RCL' => $animator?->getLastName(),
                    'Prénom RCL' => $animator?->getFirstName(),
                    'Email RCL' => $animator?->getEmailAddress(),
                    'Téléphone RCL' => PhoneNumberUtils::format($animator?->getPhone()),
                    'Labels militants RCL' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (!\in_array($tag, TagEnum::getAdherentTags(), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $animator?->tags ?? []))),
                    'Nombre de membres' => $committee->getMembersCount(),
                    'Dont adhérents à jour' => $committee->getAdherentsCount(),
                    'Nombre de sympathisants sur la zone' => $committee->getSympathizersCount(),
                    'Nombre d\'élections liées' => $committee->countElections(),
                    'Date de création' => $committee->getCreatedAt()?->format('d/m/Y H:i:s'),
                    'Date de dernière modification' => $committee->getUpdatedAt()?->format('d/m/Y H:i:s'),
                    'Créé par' => ($createdBy = $committee->getCreatedByAdherent())
                        ? \sprintf('%s (%s)', $createdBy->getFullName(), $createdBy->getPublicId())
                        : null,
                    'Modifié par' => ($updatedBy = $committee->getUpdatedByAdherent())
                        ? \sprintf('%s (%s)', $updatedBy->getFullName(), $updatedBy->getPublicId())
                        : null,
                    'UUID' => $committee->getUUID()->toString(),
                ];
            } catch (\Exception $e) {
                $this->logger->error(
                    \sprintf('Error exporting Committee with UUID: %s. (%s)', $committee->getUuid()->toString(), $e->getMessage()),
                    ['exception' => $e]
                );

                return [
                    'Nom' => $committee->getName(),
                    'UUID' => $committee->getUuid()->toString(),
                ];
            }
        }];
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}

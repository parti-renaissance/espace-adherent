<?php

namespace App\Admin;

use App\Admin\Filter\PostalCodeFilter;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Committee\Event\BeforeEditCommitteeEvent;
use App\Committee\Event\EditCommitteeEvent;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Geo\Zone;
use App\Form\Admin\RenaissanceAdherentAutocompleteType;
use App\Form\ReCountryType;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeAdmin extends AbstractAdmin
{
    private $committeeMembershipRepository;
    private $committeeRepository;
    private $adherentRepository;
    private $dispatcher;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        ObjectManager $om,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->committeeMembershipRepository = $om->getRepository(CommitteeMembership::class);
        $this->committeeRepository = $om->getRepository(Committee::class);
        $this->adherentRepository = $om->getRepository(Adherent::class);
        $this->dispatcher = $dispatcher;
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
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', null, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', null, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', null, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', null, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', null, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', null, [
                    'label' => 'Longitude',
                ])
            ->end()
        ;
    }

    protected function alterObject(object $object): void
    {
        $this->dispatcher->dispatch(new BeforeEditCommitteeEvent($object));
    }

    protected function postUpdate(object $object): void
    {
        $this->dispatcher->dispatch(new EditCommitteeEvent($object));
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
                    ->add('animator', RenaissanceAdherentAutocompleteType::class, [
                        'label' => false,
                        'required' => false,
                        'req_params' => [
                            'field' => 'animator',
                            '_sonata_admin' => 'app.admin.committee',
                        ],
                    ])
                ->end()
                ->with('Localisation', ['class' => 'col-md-5'])
                    ->add('postAddress.address', TextType::class, [
                        'required' => false,
                        'label' => 'Adresse postale',
                    ])
                    ->add('postAddress.postalCode', TextType::class, [
                        'required' => false,
                        'label' => 'Code postal',
                    ])
                    ->add('postAddress.cityName', TextType::class, [
                        'required' => false,
                        'label' => 'Ville',
                    ])
                    ->add('postAddress.country', ReCountryType::class, [
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
        $committeeMembershipRepository = $this->committeeMembershipRepository;
        $committeeRepository = $this->committeeRepository;
        $adherentRepository = $this->adherentRepository;

        $filter
            ->add('id', null, [
                'label' => 'ID',
                'show_filter' => true,
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('hostOrCreatorFirstName', CallbackFilter::class, [
                'label' => 'Prénom de l\'animateur/créateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) use ($committeeMembershipRepository, $adherentRepository, $committeeRepository) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $creatorCommitteeIds = $committeeRepository->findCommitteesUuidByCreatorUuids($adherentRepository->findAdherentsUuidByFirstName($value->getValue()));
                    $hostCommitteeIds = $committeeMembershipRepository->findCommitteesUuidByHostFirstName($value->getValue());
                    if (!$creatorCommitteeIds && !$hostCommitteeIds) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(\sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $committeeIds = array_unique(array_merge($hostCommitteeIds, $creatorCommitteeIds));
                    $qb->andWhere($qb->expr()->in(\sprintf('%s.uuid', $alias), $committeeIds));

                    return true;
                },
            ])
            ->add('hostOrCreatorLastName', CallbackFilter::class, [
                'label' => 'Nom de l\'animateur/créateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) use ($committeeMembershipRepository, $committeeRepository, $adherentRepository) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $creatorCommitteeIds = $committeeRepository->findCommitteesUuidByCreatorUuids($adherentRepository->findAdherentsUuidByLastName($value->getValue()));
                    $hostCommitteeIds = $committeeMembershipRepository->findCommitteesUuidByHostLastName($value->getValue());
                    if (!$creatorCommitteeIds && !$hostCommitteeIds) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(\sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $committeeIds = array_unique(array_merge($hostCommitteeIds, $creatorCommitteeIds));
                    $qb->andWhere($qb->expr()->in(\sprintf('%s.uuid', $alias), $committeeIds));

                    return true;
                },
            ])
            ->add('hostOrCreatorEmailAddress', CallbackFilter::class, [
                'label' => 'Email de l\'animateur/créateur',
                'show_filter' => true,
                'field_type' => EmailType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) use ($committeeMembershipRepository, $adherentRepository, $committeeRepository) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $creatorCommitteeIds = $committeeRepository->findCommitteesUuidByCreatorUuids($adherentRepository->findAdherentsUuidByEmailAddress($value->getValue()));
                    $hostCommitteeIds = $committeeMembershipRepository->findCommitteesUuidByHostEmailAddress($value->getValue());
                    if (!$creatorCommitteeIds && !$hostCommitteeIds) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(\sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $committeeIds = array_unique(array_merge($hostCommitteeIds, $creatorCommitteeIds));
                    $qb->andWhere($qb->expr()->in(\sprintf('%s.uuid', $alias), $committeeIds));

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
            ->add('postalCode', PostalCodeFilter::class, [
                'label' => 'Code postal',
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(\sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value->getValue()).'%');

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'show_filter' => true,
                'field_type' => ReCountryType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(\sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', strtolower($value->getValue()));

                    return true;
                },
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'En attente' => Committee::PENDING,
                        'Accepté' => Committee::APPROVED,
                        'Refusé' => Committee::REFUSED,
                        'Fermé' => Committee::CLOSED,
                        'Pré-approuvé' => Committee::PRE_APPROVED,
                        'Pré-refusé' => Committee::PRE_REFUSED,
                    ],
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'ID'])
            ->addIdentifier('name', null, ['label' => 'Nom'])
            ->add('animator', null, [
                'label' => 'Responsable',
            ])
            ->add('zones', null, ['label' => 'Zones'])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/committee/list_status.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/committee/list_actions.html.twig',
            ])
        ;
    }
}

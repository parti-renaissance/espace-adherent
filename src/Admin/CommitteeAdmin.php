<?php

namespace App\Admin;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Committee\Event\BeforeEditCommitteeEvent;
use App\Committee\Event\EditCommitteeEvent;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Form\Admin\RenaissanceAdherentAutocompleteType;
use App\Query\Utils\MultiColumnsSearchHelper;
use Doctrine\ORM\QueryBuilder;
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeAdmin extends AbstractAdmin
{
    private $dispatcher;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
    }

    /** @param QueryBuilder|ProxyQueryInterface $query */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect('_animator')
            ->leftJoin($alias.'.animator', '_animator')
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
                'show_filter' => false,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('updatedAt', DateRangeFilter::class, [
                'label' => 'Date de dernière modification',
                'show_filter' => false,
                'field_type' => DateRangePickerType::class,
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
            ])
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('animator', null, [
                'label' => 'Responsable',
                'template' => 'admin/committee/list_animator.html.twig',
            ])
            ->add('members', null, [
                'label' => 'Membres',
                'virtual_field' => true,
                'template' => 'admin/committee/list_members.html.twig',
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
}

<?php

namespace App\Admin;

use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Event\Event;
use App\Entity\Geo\Zone;
use App\Event\EventEvent;
use App\Event\EventVisibilityEnum;
use App\Events;
use App\Form\Admin\TipTapContentType;
use App\Form\EventCategoryType;
use App\Form\ReCountryType;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
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
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;
    private $beforeUpdate;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    /** @param QueryBuilder|ProxyQueryInterface $query */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect('_organizer')
            ->leftJoin($alias.'.author', '_organizer')
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Événement', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('committee', null, [
                    'label' => 'Comité organisateur',
                    'virtual_field' => true,
                    'template' => 'admin/event/show_committee.html.twig',
                ])
                ->add('visibility', null, [
                    'label' => 'Visibilité',
                ])
                ->add('category', null, [
                    'label' => 'Catégorie',
                ])
                ->add('description', null, ['label' => 'Description'])
                ->add('beginAt', null, [
                    'label' => 'Date de début',
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                ])
                ->add('createdAt', null, [
                    'label' => 'Date de création',
                ])
                ->add('participantsCount', null, [
                    'label' => 'Nombre de participants',
                ])
                ->add('status', 'trans', [
                    'label' => 'Statut',
                    'catalogue' => 'forms',
                ])
                ->add('published', null, [
                    'label' => 'Publié',
                ])
                ->add('electoral', null, [
                    'label' => 'Électoral',
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
                ->add('timeZone', null, [
                    'label' => 'Fuseau horaire',
                ])
            ->end()
        ;
    }

    /**
     * @param Event $object
     */
    protected function alterObject(object $object): void
    {
        if (null === $this->beforeUpdate) {
            $this->beforeUpdate = clone $object;
        }
    }

    protected function preUpdate(object $object): void
    {
        if ($this->beforeUpdate) {
            $this->dispatcher->dispatch(new EventEvent($object->getOrganizer(), $this->beforeUpdate), Events::EVENT_PRE_UPDATE);
        }
    }

    /**
     * @param Event $object
     */
    protected function postUpdate(object $object): void
    {
        $this->dispatcher->dispatch(new EventEvent($object->getOrganizer(), $object), Events::EVENT_UPDATED);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Événement', ['class' => 'col-md-7'])
                ->add('name', null, ['label' => 'Nom'])
                ->add('slug', null, ['label' => 'Slug', 'disabled' => true])
                ->add('category', EventCategoryType::class, [
                    'label' => 'Catégorie',
                ])
                ->add('committee', null, [
                    'label' => 'Comité organisateur',
                ])
                ->add('description', HiddenType::class, [
                    'attr' => ['class' => 'tiptap-html-content'],
                ])
                ->add('jsonDescription', TipTapContentType::class, ['label' => 'Description'])
                ->add('beginAt', null, [
                    'label' => 'Date de début',
                    'widget' => 'single_text',
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                    'widget' => 'single_text',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => Event::STATUSES,
                    'choice_translation_domain' => 'forms',
                    'choice_label' => function (?string $choice) {
                        return $choice;
                    },
                ])
                ->add('published', null, [
                    'label' => 'Publié',
                ])
                ->add('visibility', EnumType::class, [
                    'label' => 'Visibilité',
                    'class' => EventVisibilityEnum::class,
                ])
                ->add('national', null, ['label' => 'National'])
                ->add('liveUrl', UrlType::class, [
                    'label' => 'Live URL',
                    'required' => false,
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                    'required' => false,
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                    'required' => false,
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                    'required' => false,
                ])
                ->add('postAddress.country', ReCountryType::class, [
                    'required' => false,
                ])
                ->add('postAddress.latitude', NumberType::class, [
                    'label' => 'Latitude',
                    'required' => false,
                    'html5' => true,
                ])
                ->add('postAddress.longitude', NumberType::class, [
                    'label' => 'Longitude',
                    'required' => false,
                    'html5' => true,
                ])
                ->add('timeZone', null, [
                    'label' => 'Fuseau horaire',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('organizer', CallbackFilter::class, [
                'label' => 'Organisateur',
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
                            ['_organizer.firstName', '_organizer.lastName'],
                            ['_organizer.lastName', '_organizer.firstName'],
                            ['_organizer.emailAddress', '_organizer.emailAddress'],
                        ],
                        [
                            '_organizer.phone',
                        ],
                        [
                            '_organizer.id',
                            '_organizer.uuid',
                            '_organizer.publicId',
                        ]
                    );

                    return true;
                },
            ])
            ->add('visibility', ChoiceFilter::class, [
                'label' => 'Visibilité',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'multiple' => true,
                    'class' => EventVisibilityEnum::class,
                ],
            ])
            ->add('category', CallbackFilter::class, [
                'label' => 'Catégorie',
                'show_filter' => true,
                'field_type' => EventCategoryType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->innerJoin($alias.'.category', 'eventCategory')
                        ->andWhere('eventCategory IN (:category)')
                        ->setParameter('category', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'label' => 'Périmètres géographiques',
                'show_filter' => true,
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
            ->add('committee', ModelFilter::class, [
                'label' => 'Comité',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'name',
                    ],
                    'to_string_callback' => static function (Committee $committee): string {
                        return $committee->getName();
                    },
                ],
            ])
            ->add('agora', ModelFilter::class, [
                'label' => 'Agora',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'name',
                    ],
                    'to_string_callback' => static function (Agora $agora): string {
                        return $agora->getName();
                    },
                ],
            ])
            ->add('beginAt', DateRangeFilter::class, [
                'label' => 'Date de début',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('sendInvitationEmail', null, [
                'label' => 'Invitation Auto',
            ])
            ->add('finishAt', DateRangeFilter::class, [
                'label' => 'Date de fin',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('updatedAt', DateRangeFilter::class, [
                'label' => 'Date de dernière modification',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('_assembly', null, [
                'label' => 'Assemblée',
                'virtual_field' => true,
                'template' => 'admin/event/list_assembly.html.twig',
                'header_style' => 'min-width: 150px',
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'header_style' => 'min-width: 150px',
            ])
            ->add('_instance', null, [
                'label' => 'Instance organisatrice',
                'virtual_field' => true,
                'template' => 'admin/event/list_instance.html.twig',
                'header_style' => 'min-width: 150px',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/event/list_organizer.html.twig',
            ])
            ->add('_informations', null, [
                'label' => 'Informations',
                'virtual_field' => true,
                'template' => 'admin/event/list_informations.html.twig',
                'header_style' => 'min-width: 120px',
            ])
            ->add('participantsCount', null, [
                'label' => 'Participants',
            ])
            ->add('visibility', null, [
                'label' => 'Visibilité',
                'class' => EventVisibilityEnum::class,
                'template' => 'admin/event/list_visibility.html.twig',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
                'template' => 'admin/event/list_category.html.twig',
                'header_style' => 'min-width: 150px',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/event/list_actions.html.twig',
            ])
            ->add('sendInvitationEmail', null, [
                'label' => 'Invitation auto',
                'template' => 'admin/event/list_sendInvitationEmail.html.twig',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
                'header_style' => 'min-width: 150px',
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le',
                'header_style' => 'min-width: 150px',
            ])
            ->add('updatedAt', null, [
                'label' => 'Modifié le',
                'header_style' => 'min-width: 150px',
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [IteratorCallbackDataSource::CALLBACK => function (array $event) {
            /** @var Event $event */
            $event = $event[0];

            $organizer = $event->getOrganizer();

            try {
                return [
                    'UUID' => $event->getUUID()->toString(),
                    'Région' => implode(', ', array_map(function (Zone $zone): string {
                        return \sprintf('%s (%s)', $zone->getName(), $zone->getCode());
                    }, $event->getParentZonesOfType(Zone::REGION))),
                    'Assemblée' => ($assemblyZone = $event->getAssemblyZone())
                        ? \sprintf('%s (%s)', $assemblyZone->getName(), $assemblyZone->getCode())
                        : null,
                    'Nom' => $event->getName(),
                    'Annulé' => $event->isCancelled() ? 'oui' : 'non',
                    'Instance organisatrice' => $event->getAuthorZone(),
                    'Numéro adhérent organisateur' => $organizer?->getPublicId(),
                    'Prénom organisateur' => $organizer?->getFirstName(),
                    'Nom organisateur' => $organizer?->getLastName(),
                    'Rôle organisateur' => $event->getAuthorRole(),
                    'Lien de visio' => $event->getVisioUrl(),
                    'Lien de live' => $event->liveUrl,
                    'Visibilité' => $this->translator->trans('event.visibility.'.$event->visibility->value),
                    'Date de début' => $event->getBeginAt()?->format('d/m/Y H:i:s'),
                    'Date de fin' => $event->getFinishAt()?->format('d/m/Y H:i:s'),
                    'Date de création' => $event->getCreatedAt()?->format('d/m/Y H:i:s'),
                    'Date de dernière modification' => $event->getUpdatedAt()?->format('d/m/Y H:i:s'),
                ];
            } catch (\Exception $e) {
                $this->logger->error(
                    \sprintf('Error exporting Event with UUID: %s. (%s)', $event->getUuid()->toString(), $e->getMessage()),
                    ['exception' => $e]
                );

                return [
                    'UUID' => $event->getUuid()->toString(),
                    'Nom' => $event->getName(),
                ];
            }
        }];
    }
}

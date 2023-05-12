<?php

namespace App\Admin;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CauseEvent;
use App\Entity\Event\CoalitionEvent;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\DefaultEvent;
use App\Entity\Event\EventCategory;
use App\Event\CommitteeEventEvent;
use App\Event\EventEvent;
use App\Events;
use App\Form\EventCategoryType;
use App\Referent\ReferentTagManager;
use App\Utils\PhpConfigurator;
use Doctrine\ORM\Query\Expr\Join;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventAdmin extends AbstractAdmin
{
    private $dispatcher;
    private $referentTagManager;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        EventDispatcherInterface $dispatcher,
        ReferentTagManager $referentTagManager
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dispatcher = $dispatcher;
        $this->referentTagManager = $referentTagManager;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->with('Événement', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                    'format_title_case' => true,
                ])
                ->add('category', null, [
                    'label' => 'Catégorie',
                ])
                ->add('type', 'trans', [
                    'format' => 'event_type.%s',
                ])
                ->add('committee', null, [
                    'label' => 'Comité organisateur',
                    'virtual_field' => true,
                    'template' => 'admin/event/show_committee.html.twig',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
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
                ->add('private', null, [
                    'label' => 'Réservé aux adhérents',
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

    protected function preUpdate(object $object): void
    {
        $this->dispatcher->dispatch(new EventEvent($object->getOrganizer(), $object), Events::EVENT_PRE_UPDATE);
    }

    /**
     * @param BaseEvent $object
     */
    protected function postUpdate(object $object): void
    {
        $this->referentTagManager->assignReferentLocalTags($object);

        if ($object instanceof CommitteeEvent) {
            $event = new CommitteeEventEvent($object->getOrganizer(), $object);
        } else {
            $event = new EventEvent($object->getOrganizer(), $object);
        }

        $this->dispatcher->dispatch($event, Events::EVENT_UPDATED);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $event = $this->getSubject();

        $formMapper
            ->with('Événement', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', EventCategoryType::class, [
                    'label' => 'Catégorie',
                ])
        ;

        if (CommitteeEvent::class === $event::class) {
            $formMapper->add('committee', null, [
                'label' => 'Comité organisateur',
            ]);
        }

        $formMapper
            ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
                ->add('beginAt', null, [
                    'label' => 'Date de début',
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => BaseEvent::STATUSES,
                    'choice_translation_domain' => 'forms',
                    'choice_label' => function (?string $choice) {
                        return $choice;
                    },
                ])
                ->add('published', null, [
                    'label' => 'Publié',
                ])
                ->add('private', null, [
                    'label' => 'Réservé aux adhérents',
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', CountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', NumberType::class, [
                    'label' => 'Latitude',
                    'html5' => true,
                ])
                ->add('postAddress.longitude', NumberType::class, [
                    'label' => 'Longitude',
                    'html5' => true,
                ])
                ->add('timeZone', null, [
                    'label' => 'Fuseau horaire',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
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
                        ->leftJoin(CommitteeEvent::class, 'committeeEvent', Join::WITH, 'committeeEvent.id = '.$alias.'.id')
                        ->leftJoin(DefaultEvent::class, 'defaultEvent', Join::WITH, 'defaultEvent.id = '.$alias.'.id')
                        ->leftJoin(CauseEvent::class, 'causeEvent', Join::WITH, 'causeEvent.id = '.$alias.'.id')
                        ->leftJoin(CoalitionEvent::class, 'coalitionEvent', Join::WITH, 'coalitionEvent.id = '.$alias.'.id')
                        ->leftJoin(EventCategory::class, 'eventCategory', Join::WITH, 'eventCategory = committeeEvent.category OR eventCategory = defaultEvent.category OR eventCategory = causeEvent.category OR eventCategory = coalitionEvent.category')
                        ->andWhere('eventCategory IN (:category)')
                        ->setParameter('category', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('beginAt', DateRangeFilter::class, [
                'label' => 'Date de début',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('organizer.firstName', null, [
                'label' => 'Prénom de l\'organisateur',
                'show_filter' => true,
            ])
            ->add('organizer.lastName', null, [
                'label' => 'Nom de l\'organisateur',
                'show_filter' => true,
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
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value->getValue()).'%');

                    return true;
                },
            ])
            ->add('referent', CallbackFilter::class, [
                'label' => 'Événements du référent',
                'show_filter' => true,
                'field_type' => CheckboxType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(sprintf('%s.committee IS NULL', $alias));

                    return true;
                },
            ])
            ->add('published', BooleanFilter::class, [
                'label' => 'Publié',
            ])
            ->add('private', BooleanFilter::class, [
                'label' => 'Réservé aux adhérents',
            ])
            ->add('electoral', BooleanFilter::class, [
                'label' => 'Électoral',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id', null, [
                'label' => 'Id',
            ])
            ->add('type', 'trans', [
                'format' => 'event_type.%s',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('committee', null, [
                'label' => 'Comité organisateur',
                'virtual_field' => true,
                'template' => 'admin/event/list_committee.html.twig',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/event/list_organizer.html.twig',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('_location', null, [
                'label' => 'Lieu',
                'virtual_field' => true,
                'template' => 'admin/event/list_location.html.twig',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('participantsCount', null, [
                'label' => 'Participants',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/event/list_status.html.twig',
            ])
            ->add('private', null, [
                'label' => 'Réservé aux adhérents',
            ])
            ->add('electoral', null, [
                'label' => 'Électoral',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/event/list_actions.html.twig',
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [
            'Date' => 'beginAt',
            'Titre' => 'name',
            'Organisateur' => 'organizer.getFullName',
            'Type' => 'type',
            'Catégorie' => 'category',
            'Ville' => 'cityName',
            'Code Postal' => 'postalCode',
            'Nombre d\'inscrits' => 'participantsCount',
            'Date de création' => 'createdAt',
            'Date de modification' => 'updatedAt',
        ];
    }
}

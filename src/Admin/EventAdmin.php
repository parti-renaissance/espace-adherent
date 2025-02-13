<?php

namespace App\Admin;

use App\Admin\Filter\PostalCodeFilter;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Event\Event;
use App\Event\EventEvent;
use App\Event\EventVisibilityEnum;
use App\Events;
use App\Form\EventCategoryType;
use App\Form\ReCountryType;
use App\Utils\PhpConfigurator;
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
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\Required;

class EventAdmin extends AbstractAdmin
{
    private $dispatcher;
    private $beforeUpdate;

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
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
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
            ->add('visibility', ChoiceFilter::class, [
                'label' => 'Visibilité',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'multiple' => true,
                    'class' => EventVisibilityEnum::class,
                ],
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
            ->add('author.firstName', null, [
                'label' => 'Prénom de l\'organisateur',
                'show_filter' => true,
            ])
            ->add('author.lastName', null, [
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
            ->add('published', BooleanFilter::class, [
                'label' => 'Publié',
            ])
            ->add('electoral', BooleanFilter::class, [
                'label' => 'Électoral',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, [
                'label' => 'Id',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('committee', null, [
                'label' => 'Comité organisateur',
                'virtual_field' => true,
                'template' => 'admin/event/list_committee.html.twig',
            ])
            ->add('visibility', null, [
                'label' => 'Visibilité',
                'class' => EventVisibilityEnum::class,
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
            'Catégorie' => 'category',
            'Ville' => 'cityName',
            'Code Postal' => 'postalCode',
            'Nombre d\'inscrits' => 'participantsCount',
            'Date de création' => 'createdAt',
            'Date de modification' => 'updatedAt',
        ];
    }

    #[Required]
    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}

<?php

namespace App\Admin\Jecoute;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Administrator;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use App\Repository\Geo\ZoneRepository;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Security;

class NewsAdmin extends AbstractAdmin
{
    private $security;
    private $zoneRepository;
    private $newsHandler;

    public function __construct(
        $code,
        $class,
        $baseControllerName,
        Security $security,
        ZoneRepository $zoneRepository,
        NewsHandler $newsHandler
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->security = $security;
        $this->zoneRepository = $zoneRepository;
        $this->newsHandler = $newsHandler;
    }

    protected function getAccessMapping(): array
    {
        return [
            'pin' => 'PIN',
        ];
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->add('pin', $this->getRouterIdParameter().'/pin')
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('enriched', null, [
                    'label' => 'Enrichie',
                ])
                ->add('text', TextareaType::class, [
                    'label' => 'Texte*',
                    'required' => false,
                    'attr' => ['maxlength' => 1000],
                    'help' => '1000 caractères maximum.',
                ])
                ->add('enrichedText', TextareaType::class, [
                    'label' => 'Texte*',
                    'required' => false,
                    'attr' => [
                        'class' => 'markdown-content-editor',
                        'maxlength' => 10000,
                        'novalidate' => 'novalidate',
                    ],
                    'with_character_count' => true,
                    'help' => <<<HELP
                        Veuillez restreindre le contenu au format <a href="https://www.markdownguide.org/basic-syntax/" target="_blank">Markdown.</a><br />
                        10 000 caractères maximum.
                        HELP
                    ,
                    'help_html' => true,
                ])
                ->add('externalLink', UrlType::class, [
                    'label' => 'Lien',
                    'required' => false,
                ])
                ->add('linkLabel', TextType::class, [
                    'label' => 'Label',
                    'required' => false,
                    'help' => 'Le label du lien (30 caractères maximum).',
                ])
                ->add('pinned', null, [
                    'label' => 'Épinglée',
                    'required' => false,
                ])
            ->end()
            ->with('Zone', ['class' => 'col-md-6'])
                ->add('global', CheckboxType::class, [
                    'label' => '⚠ Sur toute la France ⚠',
                    'required' => false,
                ])
                ->add('zone', EntityType::class, [
                    'class' => Zone::class,
                    'query_builder' => $this->zoneRepository->createSelectForJeMarcheNotificationsQueryBuilder(),
                    'required' => false,
                    'group_by' => function (Zone $zone) {
                        return match ($zone->getType()) {
                            Zone::DEPARTMENT => 'Départements',
                            Zone::REGION => 'Régions',
                            default => null,
                        };
                    },
                ])
            ->end()
            ->with('Audience', ['class' => 'col-md-6'])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publication',
                    'required' => false,
                    'help' => 'Cochez cette case pour publier la notification',
                ])
                ->add('notification', CheckboxType::class, [
                    'label' => 'Notification',
                    'required' => false,
                    'help' => 'Cochez cette case pour notifier les utilisateurs mobile',
                ])
            ->end()
        ;

        $zone = $this->getSubject()->getZone();
        $form->getFormBuilder()->get('global')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($zone) {
            if (!$this->isCreation() && null === $zone) {
                $event->setData(true);
            }
        });
        $text = $this->getSubject()->getText();
        $form->getFormBuilder()->get('enrichedText')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($text) {
            if (null != $text) {
                $event->setData($text);
            }
        });

        $form->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        $form->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (isset($data['enriched']) && '1' == $data['enriched']) {
            $data['text'] = $data['enrichedText'] ?? '';

            $event->setData($data);
        }
    }

    public function submit(FormEvent $event): void
    {
        /** @var News $news */
        $news = $event->getData();

        $this->newsHandler->buildTopic($news);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('createdAt', DateRangeFilter::class, [
                'show_filter' => true,
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('createdBy', null, [
                'label' => 'Auteur',
                'show_filter' => true,
            ])
            ->add('pinned', null, [
                'label' => 'Épinglée',
                'show_filter' => true,
            ])
            ->add('zone', ZoneAutocompleteFilter::class, [
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
                'callback' => function ($admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();
                    $qb = $datagrid->getQuery();
                    $alias = $qb->getRootAlias();
                    $qb
                        ->andWhere($alias.'.type IN (:types)')
                        ->setParameter('types', [
                            Zone::REGION,
                            Zone::DEPARTMENT,
                        ])
                    ;
                    $datagrid->setValue($property, null, $value);
                },
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('text', null, [
                'label' => 'Texte',
            ])
            ->add('notification', null, [
                'label' => 'Notification',
            ])
            ->add('published', null, [
                'label' => 'Publiée',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('text', null, [
                'label' => 'Texte',
            ])
            ->add('externalLink', null, [
                'label' => 'Lien',
            ])
            ->add('zone', null, [
                'label' => 'Audience',
                'template' => 'admin/jecoute/news/list_zone.html.twig',
            ])
            ->add('notification', null, [
                'label' => 'Notification',
            ])
            ->add('published', null, [
                'label' => 'Publiée',
            ])
            ->add('pinned', null, [
                'label' => 'Épinglée',
            ])
            ->add('enriched', null, [
                'label' => 'Enrichie',
            ])
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('createdBy', null, [
                'label' => 'Auteur',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'pin' => [
                        'template' => 'admin/jecoute/news/action_pin.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /** @param News $object */
    protected function prePersist(object $object): void
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setCreatedBy($administrator);
    }

    /** @param News $object */
    protected function postPersist(object $object): void
    {
        $this->newsHandler->handleNotification($object);
        $this->newsHandler->changePinned($object);
    }

    /** @param News $object */
    protected function postUpdate(object $object): void
    {
        $this->newsHandler->changePinned($object);
    }

    /**
     * @required
     */
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }
}

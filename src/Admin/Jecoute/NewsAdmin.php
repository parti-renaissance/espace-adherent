<?php

namespace App\Admin\Jecoute;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Administrator;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use App\Repository\Geo\ZoneRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
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
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected $accessMapping = [
        'pin' => 'PIN',
    ];

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

    public function getTemplate($name)
    {
        if ('edit' === $name) {
            return 'admin/jecoute/news/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('pin', $this->getRouterIdParameter().'/pin')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
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
                ])
                ->add('externalLink', UrlType::class, [
                    'label' => 'Lien',
                    'required' => false,
                ])
                ->add('linkLabel', TextType::class, [
                    'label' => 'Label',
                    'required' => false,
                    'help' => 'Le label du lien (255 caractères maximum).',
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
                    'group_by' => function (Zone $zone, $key, $value) {
                        switch ($zone->getType()) {
                            case Zone::DEPARTMENT:
                                return 'Départements';
                            case Zone::REGION:
                                return 'Régions';
                            default:
                                return null;
                        }
                    },
                ])
            ->end()
            ->with('Audience', ['class' => 'col-md-6'])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publication',
                    'required' => false,
                    'help' => 'Cochez cette case pour publier l\'actualité',
                ])
                ->add('notification', CheckboxType::class, [
                    'label' => 'Notification',
                    'required' => false,
                    'help' => 'Cochez cette case pour notifier les utilisateurs mobile',
                ])
            ->end()
        ;

        $zone = $this->getSubject()->getZone();
        $formMapper->getFormBuilder()->get('global')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($zone) {
            if (!$this->isCreation() && null === $zone) {
                $event->setData(true);
            }
        });
        $text = $this->getSubject()->getText();
        $formMapper->getFormBuilder()->get('enrichedText')->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($text) {
            if (null != $text) {
                $event->setData($text);
            }
        });

        $formMapper->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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
                'label' => 'Audience',
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'property' => 'name',
                    'minimum_input_length' => 1,
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
                ],
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
            ->add('_action', null, [
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
    public function prePersist($object)
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setCreatedBy($administrator);
    }

    /** @param News $object */
    public function postPersist($object)
    {
        $this->newsHandler->handleNotification($object);
        $this->newsHandler->changePinned($object);
    }

    /** @param News $object */
    public function postUpdate($object)
    {
        $this->newsHandler->changePinned($object);
    }

    /**
     * @required
     */
    public function setSecurity(Security $security)
    {
        $this->security = $security;
    }
}

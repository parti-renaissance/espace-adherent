<?php

namespace App\Admin\Jecoute;

use App\Admin\AbstractAdmin;
use App\Entity\Administrator;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use App\Repository\Geo\ZoneRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
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
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('text', TextareaType::class, [
                    'label' => 'Texte',
                ])
                ->add('externalLink', UrlType::class, [
                    'label' => 'Lien',
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

        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, [$this, 'submit']);
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
            ->add('createdAt', null, [
                'label' => 'Date',
            ])
            ->add('createdBy', null, [
                'label' => 'Auteur',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
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
    }

    /**
     * @required
     */
    public function setSecurity(Security $security)
    {
        $this->security = $security;
    }
}

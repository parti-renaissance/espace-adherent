<?php

namespace App\Admin\Jecoute;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\NewsHandler;
use App\Repository\Geo\ZoneRepository;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class NewsAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly NewsHandler $newsHandler,
    ) {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
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
            ->end()
            ->with('Zone', ['class' => 'col-md-6'])
                ->add('global', CheckboxType::class, [
                    'label' => '⚠ Sur toute la France ⚠',
                    'required' => false,
                ])
                ->add('zone', EntityType::class, [
                    'label' => 'Zone géographique',
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
            if (!$this->isCreation()) {
                $event->setData(null === $zone);
            }
        });
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('createdAt', DateRangeFilter::class, [
                'show_filter' => true,
                'label' => 'Date',
                'field_type' => DateRangePickerType::class,
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
            ->add('content', null, [
                'label' => 'Contenu',
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
            ->add('content', null, [
                'label' => 'Contenu',
                'header_style' => 'width: 400px;',
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /** @param News $object */
    protected function postPersist(object $object): void
    {
        $this->newsHandler->handleNotification($object);
    }
}

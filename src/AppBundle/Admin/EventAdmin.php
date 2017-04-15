<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Event;
use AppBundle\Event\EventCategories;
use AppBundle\Form\EventCategoryType;
use AppBundle\Form\UnitedNationsCountryType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EventAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Événement', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', null, [
                    'label' => 'Catégorie',
                    'template' => 'admin/event_category.html.twig',
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
                ->add('status', 'choice', [
                    'label' => 'Statut',
                    'choices' => array_combine(Event::STATUSES, Event::STATUSES),
                    'catalogue' => 'forms',
                ])
            ->end()
            ->with('Adresse', array('class' => 'col-md-5'))
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Événement', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', ChoiceType::class, [
                    'label' => 'Catégorie',
                    'choices' => EventCategories::CHOICES,
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
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => array_combine(Event::STATUSES, Event::STATUSES),
                    'choice_translation_domain' => 'forms',
                ])
            ->end()
            ->with('Adresse', array('class' => 'col-md-5'))
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('category', null, [
                'label' => 'Type',
                'field_type' => EventCategoryType::class,
                'show_filter' => true,
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
            ->add('hostFirstName', CallbackFilter::class, [
                'label' => 'Prénom de l\'organisateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->leftJoin(sprintf('%s.organizer', $alias), 'organizer');
                    $qb->andWhere('organizer.firstName LIKE :firstname');
                    $qb->setParameter('firstname', $value['value']);

                    return true;
                },
            ])
            ->add('hostLastName', CallbackFilter::class, [
                'label' => 'Nom de l\'organisateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->leftJoin(sprintf('%s.organizer', $alias), 'organizer');
                    $qb->andWhere('organizer.lastName LIKE :lastName');
                    $qb->setParameter('lastName', $value['value']);

                    return true;
                },
            ])
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal (préfixe)',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('%s.postAddress.postalCode', $alias).' LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value['value']).'%');

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('committee', null, [
                'label' => 'Comité organisateur',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/event_organizer.html.twig',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('cityName', null, [
                'label' => 'Ville',
            ])
            ->add('categoryName', null, [
                'label' => 'Type d\'évenement',
            ])
            ->add('participantsCount', null, [
                'label' => 'Participants',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/event_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/event_actions.html.twig',
            ])
        ;
    }
}

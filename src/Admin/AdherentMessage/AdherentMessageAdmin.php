<?php

namespace App\Admin\AdherentMessage;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentMessageAdmin extends AbstractAdmin
{
    protected function getAccessMapping(): array
    {
        return [
            'display' => 'DISPLAY',
        ];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->clearExcept(['list', 'export'])
            ->add('display', $this->getRouterIdParameter().'/display')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('subject', null, ['label' => 'Event', 'show_filter' => true])
            ->add('filter.zones', ZoneAutocompleteFilter::class, [
                'label' => 'Zone géographique',
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
            ->add('sender', ModelFilter::class, [
                'label' => 'Expéditeur',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getPublicId()
                        );
                    },
                ],
            ])
            ->add('author', ModelFilter::class, [
                'label' => 'Auteur',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getPublicId()
                        );
                    },
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Créé le',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
            ->add('sentAt', DateRangeFilter::class, [
                'label' => 'Envoyé le',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
            ->add('source', ChoiceFilter::class, [
                'label' => 'Source',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => [
                        AdherentMessageInterface::SOURCE_CADRE,
                        AdherentMessageInterface::SOURCE_VOX,
                    ],
                    'choice_label' => function (string $choice) {
                        return 'adherent_message.source.'.$choice;
                    },
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('assembly', null, [
                'label' => 'Assemblée',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_assembly.html.twig',
                'header_style' => 'min-width: 200px',
            ])
            ->add('authorInstance', null, [
                'label' => 'Instance',
            ])
            ->add('informations', null, [
                'label' => 'Informations',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_informations.html.twig',
            ])
            ->add('subject', null, [
                'label' => 'Titre',
            ])
            ->add('sender', null, [
                'label' => 'Expéditeur',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_sender.html.twig',
            ])
            ->add('author', null, [
                'label' => 'Auteur',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_author.html.twig',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_status.html.twig',
            ])
            ->add('stats', null, [
                'label' => 'Stats',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_stats.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
                'display' => ['template' => 'admin/adherent_message/list_action_display.html.twig'],
            ]])
            ->add('createdAt', null, [
                'label' => 'Créé le',
            ])
            ->add('sentAt', null, [
                'label' => 'Envoyé le',
            ])
        ;
    }
}

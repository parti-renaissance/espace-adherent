<?php

namespace App\Admin;

use App\AdherentMessage\AdherentMessageStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
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
            ->clearExcept(['list'])
            ->add('display', $this->getRouterIdParameter().'/display')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('source', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Legacy' => AdherentMessageInterface::SOURCE_CADRE,
                        'Publication' => AdherentMessageInterface::SOURCE_VOX,
                    ],
                ],
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Brouillon' => AdherentMessageStatusEnum::DRAFT,
                        'Envoyé' => AdherentMessageStatusEnum::SENT,
                    ],
                ],
            ])
            ->add('subject', null, ['label' => 'Titre', 'show_filter' => true])
            ->add('filter.zones', CallbackFilter::class, [
                'label' => 'Zone géographique',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'multiple' => true,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => ['name', 'code'],
                ],
                'callback' => static function (ProxyQuery $qb, string $alias, string $field, FilterData $data): bool {
                    if (!$data->hasValue()) {
                        return false;
                    }

                    $zones = $data->getValue();

                    if ($zones instanceof Collection) {
                        $zones = $zones->toArray();
                    } elseif (!\is_array($zones)) {
                        $zones = [$zones];
                    }

                    $ids = array_map(static function (Zone $zone) {
                        return $zone->getId();
                    }, $zones);

                    /* @var QueryBuilder $qb */
                    $qb
                        ->leftJoin("$alias.$field", 'zone_filter')
                        ->leftJoin('zone_filter.parents', 'zone_parent_filter')
                        ->leftJoin("$alias.committee", 'committee')
                        ->leftJoin('committee.zones', 'committee_zone')
                        ->leftJoin('committee_zone.parents', 'committee_zone_parent')
                        ->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->in('zone_filter.id', $ids),
                                $qb->expr()->in('zone_parent_filter.id', $ids),
                                $qb->expr()->in('committee_zone.id', $ids),
                                $qb->expr()->in('committee_zone_parent.id', $ids),
                            )
                        )
                    ;

                    return true;
                },
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
            ])
            ->add('sentAt', DateRangeFilter::class, [
                'label' => 'Envoyé le',
                'field_type' => DateRangePickerType::class,
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('source', null, [
                'label' => 'Type',
                'template' => 'admin/adherent_message/list_source.html.twig',
            ])
            ->add('instance', null, [
                'label' => 'Instance',
                'virtual_field' => true,
                'template' => 'admin/adherent_message/list_assembly.html.twig',
                'header_style' => 'min-width: 200px',
            ])
            ->add('subject', null, ['label' => 'Titre'])
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
                'header_style' => 'min-width: 150px',
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

<?php

declare(strict_types=1);

namespace App\Admin\Designation;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DatePickerType;

class VotingPlatformVoteAdmin extends AbstractAdmin
{
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->innerJoin('o.electionRound', 'election_round')
            ->innerJoin('election_round.election', 'election')
            ->innerJoin('election.designation', 'designation')
            ->innerJoin('o.voter', 'voter')
            ->leftJoin('election.electionResult', 'election_result')
            ->leftJoin('election.electionEntity', 'election_entity')
            ->leftJoin('voter.adherent', 'adherent')
            ->addSelect('election_round', 'election', 'designation', 'voter', 'adherent', 'election_entity', 'election_result')
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('electionRound.election.designation', null, [
                'label' => 'Désignation',
                'show_filter' => true,
                'field_options' => [
                    'choice_label' => 'label',
                ],
            ])
            ->add('electionRound.election.electionEntity.committee', ModelFilter::class, [
                'label' => 'Comité',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => 'name',
                ],
            ])
            ->add('voter.adherent.firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('voter.adherent.lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('votedAt', CallbackFilter::class, [
                'label' => 'Date d\'émargement',
                'show_filter' => true,
                'field_type' => DatePickerType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere("DATE($alias.$field) = :date")
                        ->setParameter('date', $value->getValue())
                    ;

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id')
            ->add('electionRound.election.designation', null, [
                'label' => 'Désignation',
                'template' => 'admin/instances/vote_list_designation_column.html.twig',
            ])
            ->add('election', null, [
                'label' => 'Entité',
                'template' => 'admin/instances/vote_list_entity_column.html.twig',
            ])
            ->add('electionRound', null, [
                'label' => 'Tour',
                'template' => 'admin/instances/vote_list_round_column.html.twig',
            ])
            ->add('voter.adherent', null, [
                'label' => 'Identité',
                'template' => 'admin/instances/vote_list_identity_column.html.twig',
            ])
            ->add('votedAt', null, [
                'label' => 'Date d\'émargement',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'candidatures' => [
                        'template' => 'admin/instances/vote_list_action_column.html.twig',
                    ],
                ],
            ])
        ;
    }

    protected function configureDefaultFilterValues(array &$filterValues): void
    {
        $filterValues = array_merge($filterValues, [
            '_sort_order' => 'DESC',
            '_sort_by' => 'votedAt',
        ]);
    }
}

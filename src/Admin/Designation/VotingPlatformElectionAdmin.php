<?php

namespace App\Admin\Designation;

use App\Admin\AbstractAdmin;
use App\Entity\VotingPlatform\Election;
use App\VotingPlatform\Designation\DesignationStatusEnum;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Election\ElectionStatusEnum;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class VotingPlatformElectionAdmin extends AbstractAdmin
{
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->leftJoin('o.electionResult', 'election_result')
            ->leftJoin('o.electionPools', 'pool')
            ->leftJoin('o.electionRounds', 'election_round')
            ->leftJoin('o.electionEntity', 'election_entity')
            ->leftJoin('o.votersList', 'voters_list')
            ->leftJoin('election_entity.committee', 'committee')
            ->leftJoin('o.designation', 'designation')
            ->leftJoin('pool.candidateGroups', 'candidate_group')
            ->addSelect(
                'pool',
                'candidate_group',
                'election_round',
                'election_entity',
                'designation',
                'committee',
                'election_result',
                'voters_list',
            )
        ;

        return $query;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show']);
    }

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id', null, [
                'show_filter' => true,
            ])
            ->add('designation', null, [
                'label' => 'Désignation',
                'show_filter' => true,
                'field_options' => [
                    'choice_label' => 'label',
                ],
            ])
            ->add('electionEntity.committee', ModelFilter::class, [
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
            ->add('designation.label', null, [
                'show_filter' => true,
                'label' => 'Label',
            ])
            ->add('designation.type', ChoiceFilter::class, [
                'show_filter' => true,
                'label' => 'Type',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => DesignationTypeEnum::MAIN_TYPES,
                    'choice_label' => function (string $choice) {
                        return 'voting_platform.designation.type_'.$choice;
                    },
                ],
            ])
            ->add('status', CallbackFilter::class, [
                'show_filter' => true,
                'label' => 'Statut',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => DesignationStatusEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return "designation.status.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    /** @var QueryBuilder $qb */
                    switch ($value->getValue()) {
                        case DesignationStatusEnum::NOT_STARTED:
                            $qb
                                ->andWhere('designation.candidacyStartDate > :now')
                                ->setParameter('now', new \DateTime())
                            ;
                            break;

                        case DesignationStatusEnum::SCHEDULED:
                            $qb
                                ->andWhere('designation.candidacyStartDate <= :now AND designation.voteStartDate IS NOT NULL AND designation.voteStartDate > :now')
                                ->setParameter('now', new \DateTime())
                            ;
                            break;

                        case DesignationStatusEnum::OPENED:
                            $qb
                                ->andWhere('designation.candidacyStartDate <= :now AND designation.voteStartDate IS NULL')
                                ->setParameter('now', new \DateTime())
                            ;
                            break;

                        case DesignationStatusEnum::IN_PROGRESS:
                            $qb
                                ->andWhere('designation.voteStartDate IS NOT NULL AND designation.voteEndDate IS NOT NULL')
                                ->andWhere(\sprintf('(designation.voteStartDate < :now AND designation.voteEndDate > :now) OR (%1$s.secondRoundEndDate IS NOT NULL AND %1$s.secondRoundEndDate > :now)', $alias))
                                ->setParameter('now', new \DateTime())
                            ;
                            break;

                        case DesignationStatusEnum::CLOSED:
                            $qb
                                ->andWhere(\sprintf('%s.status = :status', $alias))
                                ->setParameter('status', ElectionStatusEnum::CLOSED)
                            ;
                            break;
                    }

                    $qb
                        ->andWhere(\sprintf('%s.status %s :cancel_status', $alias, DesignationStatusEnum::CANCELED === $value->getValue() ? '=' : '!='))
                        ->setParameter('cancel_status', ElectionStatusEnum::CANCELED)
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
            ->add('designation.label', null, [
                'label' => 'Label',
            ])
            ->add('electionEntity.name', null, [
                'label' => 'Entité',
                'template' => 'admin/instances/election_list_entity_column.html.twig',
            ])
            ->add('designation.type', 'trans', [
                'label' => 'Type',
                'format' => 'voting_platform.designation.type_%s',
            ])
            ->add('status', 'trans', [
                'label' => 'Statut',
                'format' => 'designation.status.%s',
            ])
            ->add('dates', 'array', [
                'inline' => false,
                'virtual_field' => true,
                'template' => 'admin/instances/election_list_dates_column.html.twig',
            ])
            ->add('pools', 'array', [
                'label' => 'Pools / candidatures',
                'inline' => false,
                'virtual_field' => true,
                'template' => 'admin/instances/election_list_pools_column.html.twig',
            ])
            ->add('Émargements', null, [
                'virtual_field' => true,
                'template' => 'admin/instances/election_list_details_column.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    /** @param Election $object */
    public function toString(object $object): string
    {
        return $object->getTitle();
    }
}

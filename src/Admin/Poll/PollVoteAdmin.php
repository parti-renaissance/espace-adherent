<?php

declare(strict_types=1);

namespace App\Admin\Poll;

use App\Admin\AbstractAdmin;
use App\Admin\AdherentAdmin;
use App\Entity\Adherent;
use App\Form\Admin\AdherentAutocompleteType;
use App\Repository\Poll\PollRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;

class PollVoteAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('poll', ModelFilter::class, [
                'label' => 'Sondage',
                'show_filter' => true,
                'field_options' => [
                    'query_builder' => static fn (PollRepository $repository): QueryBuilder => $repository->createSortedByFinishAtQueryBuilder(),
                ],
            ])
            ->add('adherent', ModelFilter::class, [
                'label' => 'Militant',
                'show_filter' => true,
                'field_type' => AdherentAutocompleteType::class,
                'field_options' => [
                    'class' => Adherent::class,
                    'model_manager' => $this->getModelManager(),
                    'req_params' => [
                        AdherentAdmin::ADHERENT_AUTOCOMPLETE_FILTER_METHOD_PARAM_NAME => 'autocompleteCallbackFilterPollVoters',
                    ],
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'ID'])
            ->add('poll', null, ['label' => 'Sondage'])
            ->add('adherent', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('choice', null, ['label' => 'Réponse'])
            ->add('createdAt', null, ['label' => 'Date de la réponse'])
        ;
    }
}

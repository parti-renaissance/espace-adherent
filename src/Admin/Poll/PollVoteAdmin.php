<?php

declare(strict_types=1);

namespace App\Admin\Poll;

use App\Admin\AbstractAdmin;
use App\Admin\Filter\AdherentSearchFilter;
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

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('poll', ModelFilter::class, [
                'label' => 'Sondage',
                'show_filter' => true,
            ])
            ->add('adherent', AdherentSearchFilter::class, [
                'label' => 'Recherche',
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

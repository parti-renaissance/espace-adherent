<?php

declare(strict_types=1);

namespace App\Admin\Pronostic;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PronosticParticipationAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('list');
    }

    protected function configureBatchActions(array $actions): array
    {
        return [];
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('pronostic', ModelFilter::class, [
                'label' => 'Match',
                'show_filter' => true,
            ])
            ->add('adherent', CallbackFilter::class, [
                'label' => 'Utilisateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'field_options' => ['attr' => ['placeholder' => 'Nom, prénom ou email']],
                'callback' => function (ProxyQuery $query, string $alias, string $field, FilterData $data): bool {
                    $term = $data->hasValue() ? trim((string) $data->getValue()) : '';
                    if ('' === $term) {
                        return false;
                    }

                    $query->getQueryBuilder()
                        ->leftJoin($alias.'.adherent', 'filter_adherent')
                        ->andWhere('filter_adherent.firstName LIKE :user_term OR filter_adherent.lastName LIKE :user_term OR filter_adherent.emailAddress LIKE :user_term')
                        ->setParameter('user_term', '%'.$term.'%')
                    ;

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('pronostic', null, ['label' => 'Match'])
            ->add('adherent', null, ['label' => 'Utilisateur'])
            ->add('team1Score', null, ['label' => 'Score équipe 1'])
            ->add('team2Score', null, ['label' => 'Score équipe 2'])
            ->add('resultStatus', null, ['label' => 'Résultat'])
            ->add('createdAt', null, ['label' => 'Participation enregistrée le'])
        ;
    }
}

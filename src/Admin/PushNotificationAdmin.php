<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Adherent;
use App\Entity\AppSession;
use App\Entity\Notification;
use App\Firebase\PushNotificationStatusEnum;
use App\Form\Admin\AdherentAutocompleteType;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PushNotificationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    public const AUTOCOMPLETE_ROUTE = 'admin_app_pushnotification_autocomplete';

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->clearExcept(['list', 'show'])
            ->add('autocomplete', 'autocomplete')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('notificationClass', null, ['label' => 'Type', 'show_filter' => true])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => PushNotificationStatusEnum::cases(),
                    'choice_label' => function (PushNotificationStatusEnum $status): string { return $status->value; },
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date d\'envoi',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('adherent', CallbackFilter::class, [
                'label' => 'Militant',
                'show_filter' => true,
                'field_type' => AdherentAutocompleteType::class,
                'field_options' => [
                    'class' => Adherent::class,
                    'model_manager' => $this->getModelManager(),
                ],
                'callback' => function (ProxyQueryInterface $qb, string $alias, string $field, FilterData $value): bool {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(\sprintf('EXISTS (
                        SELECT 1 FROM %s adh_n
                        WHERE adh_n.pushNotification = %s
                        AND EXISTS (
                            SELECT 1 FROM %s adh_s
                            JOIN adh_s.pushTokenLinks adh_link
                            JOIN adh_link.pushToken adh_pt
                            WHERE adh_s.adherent = :adherent
                            AND FIND_IN_SET(adh_pt.identifier, adh_n.tokens) > 0
                        )
                    )', Notification::class, $alias, AppSession::class))
                        ->setParameter('adherent', $value->getValue())
                    ;

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('notificationClass', null, ['label' => 'Type'])
            ->add('title', null, ['label' => 'Titre'])
            ->add('scope', null, ['label' => 'Scope'])
            ->add('totalTokens', null, ['label' => 'Tokens'])
            ->add('totalSuccess', null, ['label' => 'Succès'])
            ->add('totalFailed', null, ['label' => 'Échoués'])
            ->add('status', 'enum', [
                'label' => 'Statut',
                'use_value' => true,
                'enum_translation_domain' => 'messages',
            ])
            ->add('chunksDelivered', null, ['label' => 'Chunks livrés'])
            ->add('chunksTotal', null, ['label' => 'Chunks total'])
            ->add('createdAt', null, ['label' => 'Date'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
                'show' => [],
                'adherents' => ['template' => 'admin/push_notification/list_action_adherents.html.twig'],
            ]])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Envoi', ['class' => 'col-md-8'])
                ->add('notificationClass', null, ['label' => 'Type'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('body', null, ['label' => 'Contenu'])
                ->add('scope', null, ['label' => 'Scope'])
                ->add('data', null, ['label' => 'Data'])
            ->end()
            ->with('Statistiques', ['class' => 'col-md-4'])
                ->add('status', 'enum', [
                    'label' => 'Statut',
                    'use_value' => true,
                    'enum_translation_domain' => 'messages',
                ])
                ->add('totalTokens', null, ['label' => 'Total tokens'])
                ->add('totalSuccess', null, ['label' => 'Succès'])
                ->add('totalFailed', null, ['label' => 'Échoués'])
                ->add('chunksDelivered', null, ['label' => 'Chunks livrés'])
                ->add('chunksTotal', null, ['label' => 'Chunks total'])
                ->add('createdAt', null, ['label' => 'Date d\'envoi'])
            ->end()
        ;
    }
}

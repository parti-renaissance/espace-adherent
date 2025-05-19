<?php

namespace App\Admin;

use App\AppCodeEnum;
use App\AppSession\SessionStatusEnum;
use App\AppSession\SystemEnum;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\Query\Expr\Join;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentAdmin extends AbstractAdherentAdmin
{
    protected function getAccessMapping(): array
    {
        return [
            'ban' => 'BAN',
            'terminate_membership' => 'TERMINATE_MEMBERSHIP',
            'certify' => 'CERTIFY',
            'uncertify' => 'UNCERTIFY',
            'extract' => 'EXTRACT',
            'create_renaissance' => 'CREATE_RENAISSANCE',
            'create_renaissance_verify_email' => 'CREATE_RENAISSANCE_VERIFY_EMAIL',
            'refresh_tags' => 'REFRESH_TAGS',
        ];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        parent::configureRoutes($collection);

        $collection
            ->add('ban', $this->getRouterIdParameter().'/ban')
            ->add('terminate_membership', $this->getRouterIdParameter().'/terminate-membership')
            ->add('certify', $this->getRouterIdParameter().'/certify')
            ->add('uncertify', $this->getRouterIdParameter().'/uncertify')
            ->add('extract', 'extract')
            ->add('send_resubscribe_email', $this->getRouterIdParameter().'/send-resubscribe-email')
            ->add('create_renaissance', 'create-renaissance')
            ->add('create_renaissance_verify_email', 'create-adherent-verify-email')
            ->add('refresh_tags', $this->getRouterIdParameter().'/refresh-tags')
        ;
    }

    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        if (\in_array($action, ['ban', 'certify', 'uncertify'], true)) {
            $actions = parent::configureActionButtons($buttonList, 'show', $object);
        } else {
            $actions = parent::configureActionButtons($buttonList, $action, $object);
        }

        if (\in_array($action, ['edit', 'show', 'ban', 'certify', 'uncertify'], true)) {
            $actions['switch_user'] = ['template' => 'admin/adherent/action_button_switch_user.html.twig'];
        }

        if (\in_array($action, ['edit', 'show'], true)) {
            if ($this->hasAccess('ban', $object) && $this->hasRoute('ban')) {
                $actions['ban'] = ['template' => 'admin/adherent/action_button_ban.html.twig'];
            }

            if ($this->hasAccess('terminate_membership', $object) && $this->hasRoute('terminate_membership')) {
                $actions['terminate_membership'] = ['template' => 'admin/adherent/action_button_terminate_membership.html.twig'];
            }

            if ($this->hasAccess('certify', $object) && $this->hasRoute('certify')) {
                $actions['certify'] = ['template' => 'admin/adherent/action_button_certify.html.twig'];
            }

            if ($this->hasAccess('uncertify', $object) && $this->hasRoute('uncertify')) {
                $actions['uncertify'] = ['template' => 'admin/adherent/action_button_uncertify.html.twig'];
            }

            if ($this->hasAccess('refresh_tags', $object) && $this->hasRoute('refresh_tags')) {
                $actions['refresh_tags'] = ['template' => 'admin/adherent/action_button_refresh_tags.html.twig'];
            }
        }

        $actions['extract'] = ['template' => 'admin/adherent/extract/extract_button.html.twig'];

        return $actions;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('sandboxMode', BooleanFilter::class)
            ->add('committeeMembership.committee', CallbackFilter::class, [
                'label' => 'Comité',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Committee::class,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $committee = $value->getValue();

                    $qb
                        ->andWhere("$alias.committee = :committee")
                        ->setParameter('committee', $committee)
                    ;

                    return true;
                },
            ])
            ->add('activeSession', CallbackFilter::class, [
                'label' => 'Session active',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge(array_map(fn (SystemEnum $system) => $system->value, SystemEnum::all()), [0 => 'aucune']),
                    'choice_label' => fn (string $system) => $system,
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->leftJoin("$alias.appSessions", 'active_session_filter', Join::WITH, 'active_session_filter.status = :active_session_filter_status')
                        ->leftJoin('active_session_filter.client', 'active_session_filter_client', Join::WITH, 'active_session_filter_client.code = :session_client_code')
                        ->setParameter('active_session_filter_status', SessionStatusEnum::ACTIVE)
                        ->setParameter('session_client_code', AppCodeEnum::BESOIN_D_EUROPE)
                    ;

                    if (\in_array('aucune', $value->getValue(), true)) {
                        $qb->andWhere('active_session_filter IS NULL OR active_session_filter_client IS NULL');
                    } else {
                        $qb
                            ->andWhere('active_session_filter.appSystem IN (:active_session_filter_systems) AND active_session_filter_client IS NOT NULL')
                            ->setParameter('active_session_filter_systems', $value->getValue())
                        ;
                    }

                    return true;
                },
            ])
            ->add('subscriptionPush', CallbackFilter::class, [
                'label' => 'Abonnement Push',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ['Abonné' => 1, 'Désabonné' => 0],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->innerJoin("$alias.appSessions", 'session_push_subscription', Join::WITH, 'session_push_subscription.status = :subscription_push_filter_status')
                        ->innerJoin('session_push_subscription.client', 'session_push_subscription_client', Join::WITH, 'session_push_subscription_client.code = :session_client_code')
                        ->setParameter('subscription_push_filter_status', SessionStatusEnum::ACTIVE)
                        ->setParameter('session_client_code', AppCodeEnum::BESOIN_D_EUROPE)
                    ;

                    if ($value->getValue()) {
                        $qb
                            ->innerJoin('session_push_subscription.pushTokenLinks', 'push_token_link', Join::WITH, 'push_token_link.unsubscribedAt IS NULL')
                            ->andWhere('session_push_subscription.unsubscribedAt IS NULL')
                        ;
                    } else {
                        $qb
                            ->leftJoin("$alias.appSessions", 'session_push_subscription2', Join::WITH, 'session_push_subscription2.id != session_push_subscription.id AND session_push_subscription2.status = :subscription_push_filter_status AND session_push_subscription2.unsubscribedAt IS NULL')
                            ->leftJoin('session_push_subscription2.client', 'session_push_subscription_client2', Join::WITH, 'session_push_subscription_client2.code = :session_client_code')
                            ->leftJoin('session_push_subscription2.pushTokenLinks', 'push_token_link2', Join::WITH, 'push_token_link2.unsubscribedAt IS NULL')
                            ->andWhere('session_push_subscription_client2 IS NULL OR push_token_link2 IS NULL')
                        ;
                    }

                    return true;
                },
            ])
            ->add('subscriptionSMS', CallbackFilter::class, [
                'label' => 'Abonnement SMS',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ['Abonné' => 1, 'Désabonné' => 0, 'Sans téléphone' => 2],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    if (2 === $value->getValue()) {
                        $qb->andWhere("$alias.phone IS NULL");
                    } else {
                        $qb
                            ->leftJoin("$alias.subscriptionTypes", 'subscription_type', Join::WITH, 'subscription_type.code = :sms_st_code')
                            ->setParameter('sms_st_code', SubscriptionTypeEnum::MILITANT_ACTION_SMS)
                            ->andWhere('subscription_type '.($value->getValue() ? 'IS NOT' : 'IS').' NULL')
                        ;
                    }

                    return true;
                },
            ])
            ->add('mailchimpStatus', ChoiceFilter::class, [
                'label' => 'Abonnement email',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ContactStatusEnum::values(),
                    'choice_label' => function (string $label) {
                        return 'mailchimp_contact.status.'.$label;
                    },
                ],
            ])
            ->add('agoraMemberships.agora', CallbackFilter::class, [
                'label' => 'Agora',
                'show_filter' => true,
                'field_type' => EntityType::class,
                'field_options' => [
                    'class' => Agora::class,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $agora = $value->getValue();

                    $qb
                        ->andWhere("$alias.agora = :agora")
                        ->setParameter('agora', $agora)
                    ;

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('_zones', null, [
                'label' => 'Zone/Instance',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_zones.html.twig',
                'header_style' => 'min-width: 125px',
            ])
        ;

        $list->reorder([
            'publicId',
            'lastName',
            '_zones',
            'type',
            'allMandates',
            'subscriptionStatus',
            ListMapper::NAME_ACTIONS,
            'registeredAt',
            'firstMembershipDonation',
            'lastMembershipDonation',
            'lastLoggedAt',
        ]);
    }
}

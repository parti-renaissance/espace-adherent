<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;

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
                'field_type' => ModelAutocompleteType::class,
                'show_filter' => true,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => 'name',
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
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('postAddress', null, [
                'label' => 'Ville (CP) Pays',
                'template' => 'admin/adherent/list_postaddress.html.twig',
                'header_style' => 'min-width: 75px',
            ])
            ->add('committee', null, [
                'label' => 'Comité',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_committee.html.twig',
            ])
        ;

        $list->reorder([
            'publicId',
            'lastName',
            'postAddress',
            'committee',
            'type',
            'allMandates',
            'mailchimpStatus',
            ListMapper::NAME_ACTIONS,
            'registeredAt',
            'firstMembershipDonation',
            'lastMembershipDonation',
            'lastLoggedAt',
        ]);
    }
}

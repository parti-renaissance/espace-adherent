<?php

namespace App\Admin;

use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Contribution\ContributionStatusEnum;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Form\AdherentMandateType;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use App\Repository\Helper\MembershipFilterHelper;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdherentElectedRepresentativeAdmin extends AdherentAdmin
{
    protected $baseRoutePattern = 'adherents-elus';
    protected $baseRouteName = 'adherents-elus';

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('ban')
            ->remove('terminate_membership')
            ->remove('certify')
            ->remove('uncertify')
            ->remove('extract')
            ->remove('send_resubscribe_email')
            ->remove('create_renaissance')
            ->remove('create_renaissance_verify_email')
        ;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $rootAlias = $query->getRootAliases()[0];

        $query
            ->innerJoin(
                ElectedRepresentativeAdherentMandate::class,
                'er_adherent_mandate',
                Expr\Join::WITH,
                sprintf('%s.id = er_adherent_mandate.adherent', $rootAlias)
            )
            ->andWhere('er_adherent_mandate.finishAt IS NULL')
        ;

        return $query;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
                'show_filter' => true,
            ])
            ->add('mailchimpStatus', ChoiceFilter::class, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ContactStatusEnum::values(),
                    'choice_label' => function (string $label) {
                        return 'mailchimp_contact.status.'.$label;
                    },
                ],
                'label' => 'Abonnement e-mail',
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'label' => 'Périmètres géographiques',
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
            ->add('mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) déclaré(s)',
                'field_type' => AdherentMandateType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $where = new Expr\Orx();

                    foreach ($value->getValue() as $mandate) {
                        $where->add("FIND_IN_SET(:mandate_$mandate, $alias.mandates) > 0");
                        $qb->setParameter("mandate_$mandate", $mandate);
                    }

                    $qb->andWhere($where);

                    return true;
                },
            ])
            ->add('er_adherent_mandate_type', CallbackFilter::class, [
                'label' => 'Mandat(s)',
                'show_filter' => true,
                'field_type' => AdherentMandateType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->innerJoin(
                            ElectedRepresentativeAdherentMandate::class,
                            'er_adherent_mandate',
                            Expr\Join::WITH,
                            sprintf('%s.id = er_adherent_mandate.adherent', $alias)
                        )
                        ->andWhere('er_adherent_mandate.finishAt IS NULL')
                        ->andWhere('er_adherent_mandate.mandateType IN (:er_adherent_mandate_types)')
                        ->setParameter('er_adherent_mandate_types', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('revenueDeclared', CallbackFilter::class, [
                'label' => 'Revenus élu déclarés ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->andWhere("$alias.contributedAt IS NOT NULL");

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->andWhere("$alias.contributedAt IS NULL");

                            break;
                    }

                    return true;
                },
            ])
            ->add('contributionEligible', CallbackFilter::class, [
                'label' => 'Éligible à la cotisation élu ?',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere("$alias.contributionStatus = :contribution_status");
                    switch ($value->getValue()) {
                        case BooleanType::TYPE_YES:
                            $qb->setParameter('contribution_status', ContributionStatusEnum::ELIGIBLE);

                            break;
                        case BooleanType::TYPE_NO:
                            $qb->setParameter('contribution_status', ContributionStatusEnum::NOT_ELIGIBLE);

                            break;
                    }

                    return true;
                },
            ])
            ->add('renaissanceMembership', CallbackFilter::class, [
                'label' => 'Renaissance',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => RenaissanceMembershipFilterEnum::CHOICES,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    return MembershipFilterHelper::withMembershipFilter($qb, $alias, $value->getValue());
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('lastName', null, [
                'label' => 'Nom Prénom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('phone', null, [
                'label' => 'Téléphone',
                'template' => 'admin/adherent/list_phone.html.twig',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'adhésion',
            ])
            ->add('lastMembershipDonation', null, [
                'label' => 'Dernière cotisation',
            ])
            ->add('lastLoggedAt', null, [
                'label' => 'Dernière connexion',
            ])
            ->add('mandates', null, [
                'label' => 'Mandats déclarés',
                'template' => 'admin/adherent/list_declared_mandates.html.twig',
            ])
            ->add('allMandates', null, [
                'label' => 'Mandats',
                'virtual_field' => true,
                'template' => 'admin/adherent/list_mandates.html.twig',
            ])
            ->add('mailchimpStatus', null, [
                'label' => 'Abonnement',
                'template' => 'admin/adherent/list_email_subscription_status.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/adherent/list_actions.html.twig',
            ])
        ;
    }
}

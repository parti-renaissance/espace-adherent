<?php

namespace App\Admin;

use App\Admin\Filter\AdherentRoleFilter;
use App\Entity\Adherent;
use App\Entity\AdherentTag;
use App\Entity\Committee;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Instance\InstanceQuality;
use App\Entity\SubscriptionType;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Form\AdherentMandateType;
use App\Instance\InstanceQualityScopeEnum;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
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
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

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
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('certified', CallbackFilter::class, [
                'label' => 'Certifié',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return "global.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    switch ($value->getValue()) {
                        case 'yes':
                            $qb->andWhere("$alias.certifiedAt IS NOT NULL");

                            return true;

                        case 'no':
                            $qb->andWhere("$alias.certifiedAt IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('certifiedAt', DateRangeFilter::class, [
                'label' => 'Date de certification',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('nickname', null, [
                'label' => 'Pseudo',
            ])
            ->add('registeredAt', DateRangeFilter::class, [
                'label' => 'Date d\'adhésion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('lastLoggedAt', DateRangeFilter::class, [
                'label' => 'Dernière connexion',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.mb_strtolower($value->getValue()).'%');

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'field_type' => CountryType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', mb_strtolower($value->getValue()));

                    return true;
                },
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags admin',
                'field_options' => [
                    'class' => AdherentTag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('subscriptionTypes', ModelFilter::class, [
                'label' => 'Types de souscriptions',
                'field_options' => [
                    'class' => SubscriptionType::class,
                    'multiple' => true,
                    'choice_label' => 'label',
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
            ->add('canaryTester')
            ->add('status', ChoiceFilter::class, [
                'label' => 'Etat du compte',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Activé' => Adherent::ENABLED,
                        'Désactivé' => Adherent::DISABLED,
                    ],
                ],
            ])
            ->add('adherent', null, [
                'label' => 'Est adhérent ?',
            ])
            ->add('referentTags', ModelFilter::class, [
                'label' => 'Tags souscrits',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => 'name',
                ],
            ])
            ->add('role', AdherentRoleFilter::class, [
                'label' => 'common.role',
            ])
            ->add('elected_representative_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) RNE (legacy)',
                'field_type' => AdherentMandateType::class,
                'field_options' => [
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->leftJoin(ElectedRepresentative::class, 'er', Expr\Join::WITH, sprintf('%s.id = er.adherent', $alias))
                        ->leftJoin('er.mandates', 'mandate')
                        ->andWhere('mandate.finishAt IS NULL')
                        ->andWhere('mandate.onGoing = 1')
                        ->andWhere('mandate.isElected = 1')
                        ->andWhere('mandate.type IN (:types)')
                        ->setParameter('types', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('adherent_mandates', CallbackFilter::class, [
                'label' => 'Mandat(s) internes',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge(
                        TerritorialCouncilQualityEnum::POLITICAL_COMMITTEE_ELECTED_MEMBERS,
                        ['TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT]
                    ),
                    'choice_label' => function (string $choice) {
                        if ('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT === $choice) {
                            return 'territorial_council.membership.quality.elected_candidate_adherent';
                        } else {
                            return 'political_committee.membership.quality.'.$choice;
                        }
                    },
                    'multiple' => true,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $mandatesCondition = 'adherentMandate.quality IN (:qualities)';
                    if (\in_array('TC_'.TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT, $value->getValue())) {
                        $mandatesCondition = '(adherentMandate.quality IN (:qualities) OR adherentMandate.committee IS NOT NULL)';
                    }

                    $qb
                        ->leftJoin("$alias.adherentMandates", 'adherentMandate')
                        ->andWhere('adherentMandate.finishAt IS NULL')
                        ->andWhere($mandatesCondition)
                        ->setParameter('qualities', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('instanceQualities', CallbackFilter::class, [
                'label' => 'Membre du Conseil national',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_merge([
                        'Oui' => true,
                        'Non' => false,
                    ], array_combine($qualities = $this->instanceQualityRepository->getAllCustomQualities(), $qualities)),
                    'group_by' => function ($choice) {
                        if (\is_bool($choice)) {
                            return 'Général';
                        }

                        return 'Qualités personnalisées';
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (null === $value->getValue()) {
                        return false;
                    }

                    $qb
                        ->leftJoin("$alias.instanceQualities", 'adherent_instance_quality')
                        ->leftJoin('adherent_instance_quality.instanceQuality', 'instance_quality', Expr\Join::WITH, 'FIND_IN_SET(:national_council_scope, instance_quality.scopes) > 0')
                        ->andWhere('instance_quality.id '.(0 === $value->getValue() ? 'IS NULL' : 'IS NOT NULL'))
                        ->setParameter('national_council_scope', InstanceQualityScopeEnum::NATIONAL_COUNCIL)
                    ;

                    if ($value->getValue() instanceof InstanceQuality) {
                        $qb
                            ->andWhere('instance_quality = :instance_quality')
                            ->setParameter('instance_quality', $value->getValue())
                        ;
                    }

                    return true;
                },
            ])
            ->add('memberships.committee', CallbackFilter::class, [
                'label' => 'Comité de vote',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => Committee::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias().'.status = :approved')
                            ->setParameter('approved', Committee::APPROVED)
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.committee IN (:committees)")
                        ->andWhere("$alias.enableVote = 1")
                        ->setParameter('committees', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('territorialCouncilMembership.territorialCouncil', CallbackFilter::class, [
                'label' => 'Conseil territorial',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => TerritorialCouncil::class,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'codes',
                    ],
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.territorialCouncil IN (:tc)")
                        ->setParameter('tc', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('politicalCommitteeMembership.politicalCommittee', CallbackFilter::class, [
                'label' => 'Comité politique',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'admin_code' => $this->getCode(),
                    'context' => 'filter',
                    'class' => PoliticalCommittee::class,
                    'multiple' => true,
                    'property' => 'name',
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'callback' => function ($admin, $property, $value) {
                        $datagrid = $admin->getDatagrid();
                        $queryBuilder = $datagrid->getQuery();
                        $queryBuilder
                            ->andWhere($queryBuilder->getRootAlias().'.isActive = 1')
                            ->orderBy($queryBuilder->getRootAlias().'.name', 'ASC')
                        ;
                        $datagrid->setValue($property, null, $value);
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb
                        ->andWhere("$alias.politicalCommittee IN (:pc)")
                        ->setParameter('pc', $value->getValue())
                    ;

                    return true;
                },
            ])
            ->add('activeMembership', CallbackFilter::class, [
                'label' => 'Cotisation à jour',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'yes',
                        'no',
                    ],
                    'choice_label' => function (string $choice) {
                        return "global.$choice";
                    },
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    switch ($value->getValue()) {
                        case 'yes':
                            $qb->andWhere("$alias.lastMembershipDonation IS NOT NULL");

                            return true;

                        case 'no':
                            $qb->andWhere("$alias.lastMembershipDonation IS NULL");

                            return true;
                        default:
                            return false;
                    }
                },
            ])
            ->add('lastMembershipDonation', DateRangeFilter::class, [
                'label' => 'Date de dernière cotisation',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
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
            ->add('referentTags', null, [
                'label' => 'Tags souscrits',
                'associated_property' => 'code',
            ])
            ->add('type', null, [
                'label' => 'Rôles',
                'template' => 'admin/adherent/list_status.html.twig',
            ])
        ;
    }
}

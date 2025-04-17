<?php

namespace App\Admin;

use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Referral;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Repository\ReferralRepository;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdherentReferrerAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'adherents-parrains';
    protected $baseRouteName = 'adherents-parrains';

    public function __construct(?string $code, ?string $class, ?string $baseControllerName, private readonly ReferralRepository $referralRepository)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list']);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';

        $sortValues[DatagridInterface::SORT_BY] = 'referralsCountAdhesionFinished';
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $rootAlias = $query->getRootAliases()[0];

        $query
            ->innerJoin(
                Referral::class,
                'referral',
                Expr\Join::WITH,
                \sprintf('%s.id = referral.referrer', $rootAlias)
            )
            ->addSelect('COUNT(DISTINCT IF(referral.status = :status_adhesion_finished, referral.id, NULL)) AS referralsCountAdhesionFinished')
            ->addSelect('COUNT(DISTINCT IF(referral.status = :status_reported, referral.id, NULL)) AS referralsCountReported')
            ->addSelect('COUNT(DISTINCT IF(referral.type IN (:types_invitation), referral.id, NULL)) AS referralsCountInvitation')
            ->setParameter('status_adhesion_finished', StatusEnum::ADHESION_FINISHED)
            ->setParameter('status_reported', StatusEnum::REPORTED)
            ->setParameter('types_invitation', [TypeEnum::INVITATION, TypeEnum::PREREGISTRATION])
            ->groupBy($rootAlias.'.id')
        ;

        return $query;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.emailAddress", "$alias.emailAddress"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                            "$alias.publicId",
                        ]
                    );

                    return true;
                },
            ])
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse email',
            ])
            ->add('zones', ZoneAutocompleteFilter::class, [
                'label' => 'Périmètres géographiques',
                'show_filter' => true,
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
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, [
                'label' => 'ID',
                'template' => 'admin/adherent/list_identifier.html.twig',
            ])
            ->add('lastName', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('referralsCountAdhesionFinished', 'string', [
                'label' => 'Nombre d\'adhésions',
                'sortable' => true,
                'mapped' => false,
                'sort_field_mapping' => [],
                'sort_parent_association_mappings' => [],
            ])
            ->add('referralsCountInvitation', 'string', [
                'label' => 'Nombre d\'invitations',
                'sortable' => true,
                'mapped' => false,
                'sort_field_mapping' => [],
                'sort_parent_association_mappings' => [],
            ])
            ->add('referralsCountReported', 'string', [
                'label' => 'Nombre de signalements',
                'sortable' => true,
                'mapped' => false,
                'sort_field_mapping' => [],
                'sort_parent_association_mappings' => [],
            ])
        ;
    }
}

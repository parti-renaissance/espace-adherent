<?php

declare(strict_types=1);

namespace App\Admin;

use App\Adherent\Referral\StatusEnum;
use App\Adherent\Referral\TypeEnum;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\ZoneAutocompleteFilter;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Referral;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Repository\ReferralRepository;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use App\ValueObject\Genders;
use Doctrine\ORM\Query\Expr;
use Psr\Log\LoggerInterface;
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
    use IterableCallbackDataSourceTrait;

    private const SORTABLE_VIRTUAL_FIELDS = [
        'referralsCountAdhesionFinished',
        'referralsCountReported',
        'referralsCountInvitation',
    ];

    protected $baseRoutePattern = 'adherents-parrains';
    protected $baseRouteName = 'adherents-parrains';

    public function __construct(
        private readonly ReferralRepository $referralRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'export']);
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
        ;

        if ($this->isCurrentRoute('list')) {
            $query
                ->addSelect('COUNT(DISTINCT IF(referral.status = :status_adhesion_finished, referral.id, NULL)) AS referralsCountAdhesionFinished')
                ->addSelect('COUNT(DISTINCT IF(referral.status = :status_reported, referral.id, NULL)) AS referralsCountReported')
                ->addSelect('COUNT(DISTINCT IF(referral.type IN (:types_invitation), referral.id, NULL)) AS referralsCountInvitation')
                ->setParameter('status_adhesion_finished', StatusEnum::ADHESION_FINISHED)
                ->setParameter('status_reported', StatusEnum::REPORTED)
                ->setParameter('types_invitation', [TypeEnum::INVITATION, TypeEnum::PREREGISTRATION])
                ->groupBy($rootAlias.'.id')
            ;
        }

        if ($this->isCurrentRoute('list')) {
            $filter = $this->getRequest()->query->all('filter');
            $sortBy = $filter['_sort_by'] ?? null;
            $sortOrder = $filter['_sort_order'] ?? 'ASC';

            if (\in_array($sortBy, self::SORTABLE_VIRTUAL_FIELDS, true)) {
                $query->getQueryBuilder()->resetDQLPart('orderBy');

                $query->orderBy($sortBy, $sortOrder);
            }
        }

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
            ->add('publicId', null, ['label' => 'PID'])
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

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        $translator = $this->getTranslator();

        return [IteratorCallbackDataSource::CALLBACK => function (array $adherent) use ($translator) {
            /** @var Adherent $adherent */
            $adherent = $adherent[0];

            try {
                return [
                    'Région' => implode(', ', array_map(function (Zone $zone): string {
                        return $zone->getCode().' - '.$zone->getName();
                    }, $adherent->getParentZonesOfType(Zone::REGION))),
                    'Département' => implode(', ', array_map(function (Zone $zone): string {
                        return $zone->getCode().' - '.$zone->getName();
                    }, $adherent->getParentZonesOfType(Zone::DEPARTMENT))),
                    'PID' => $adherent->getPublicId(),
                    'Civilité' => $translator->trans(array_search($adherent->getGender(), Genders::CIVILITY_CHOICES, true)),
                    'Prénom' => $adherent->getFirstName(),
                    'Nom' => $adherent->getLastName(),
                    'Email' => $adherent->getEmailAddress(),
                    'Téléphone' => PhoneNumberUtils::format($adherent->getPhone()),
                    'Nombre d\'adhésions' => $this->referralRepository->countForReferrer($adherent, [StatusEnum::ADHESION_FINISHED]),
                    'Nombre d\'invitations' => $this->referralRepository->countForReferrer($adherent, [], [TypeEnum::INVITATION, TypeEnum::PREREGISTRATION]),
                    'Nombre de signalements' => $this->referralRepository->countForReferrer($adherent, [StatusEnum::REPORTED]),
                ];
            } catch (\Exception $e) {
                $this->logger->error(
                    \sprintf('Error exporting Adherent Referrer with UUID: %s. (%s)', $adherent->getUuid(), $e->getMessage()),
                    ['exception' => $e]
                );

                return [
                    'ID' => $adherent->getId(),
                    'UUID' => $adherent->getUuid()->toString(),
                    'Email' => $adherent->getEmailAddress(),
                ];
            }
        }];
    }
}

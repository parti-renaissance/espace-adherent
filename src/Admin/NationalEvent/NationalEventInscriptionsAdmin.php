<?php

namespace App\Admin\NationalEvent;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Admin\AbstractAdmin;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\NationalEvent\EventInscription;
use App\Form\GenderCivilityType;
use App\Form\NationalEvent\QualityChoiceType;
use App\Form\TelNumberType;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\NationalEvent\QualityEnum;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Repository\Geo\ZoneRepository;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use App\ValueObject\Genders;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NullFilter;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Messenger\MessageBusInterface;

class NationalEventInscriptionsAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    public function __construct(
        private readonly ZoneRepository $zoneRepository,
        private readonly TagTranslator $tagTranslator,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    public function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'edit', 'export']);
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

                    $qb->getQueryBuilder()->leftJoin($alias.'.adherent', 'adherent');

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.addressEmail", "$alias.addressEmail"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                            "$alias.publicId",
                            'adherent.publicId',
                        ]
                    );

                    return true;
                },
            ])
            ->add('event', null, ['label' => 'Event', 'show_filter' => true])
            ->add('ticketScannedAt', NullFilter::class, ['label' => 'Présent', 'inverse' => true, 'show_filter' => true])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => array_combine(InscriptionStatusEnum::STATUSES, InscriptionStatusEnum::STATUSES),
                ],
            ])
            ->add('paymentStatus', ChoiceFilter::class, [
                'label' => 'Statut du paiement',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => PaymentStatusEnum::all(),
                    'choice_label' => fn (PaymentStatusEnum $status) => $status,
                ],
            ])
            ->add('volunteer', BooleanFilter::class, ['label' => 'Souhaite être bénévole'])
            ->add('accessibility', NullFilter::class, ['label' => 'Handicap', 'inverse' => true])
            ->add('visitDay', ChoiceFilter::class, [
                'label' => 'Jour de présence',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $qb = $this->getModelManager()->createQuery(EventInscription::class, 'e');
                        $qb->select('DISTINCT e.visitDay')->where('e.visitDay IS NOT NULL')->orderBy('e.visitDay', 'ASC');

                        $choices = array_column($qb->getQuery()->getScalarResult(), 'visitDay');

                        return array_combine($choices, $choices);
                    }),
                ],
            ])
            ->add('transport', ChoiceFilter::class, [
                'label' => 'Forfait transport',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $qb = $this->getModelManager()->createQuery(EventInscription::class, 'e');
                        $qb->select('DISTINCT e.transport')->where('e.transport IS NOT NULL')->orderBy('e.transport', 'ASC');

                        $choices = array_column($qb->getQuery()->getScalarResult(), 'transport');

                        return array_combine($choices, $choices);
                    }),
                ],
            ])
            ->add('accommodation', ChoiceFilter::class, [
                'label' => 'Forfait hébergement',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $qb = $this->getModelManager()->createQuery(EventInscription::class, 'e');
                        $qb->select('DISTINCT e.accommodation')->where('e.accommodation IS NOT NULL')->orderBy('e.accommodation', 'ASC');

                        $choices = array_column($qb->getQuery()->getScalarResult(), 'accommodation');

                        return array_combine($choices, $choices);
                    }),
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('event', null, ['label' => 'Event'])
            ->add('identity', null, [
                'label' => 'Inscrit',
                'template' => 'admin/national_event/list_identity.html.twig',
                'virtual_field' => true,
            ])
            ->add('adherent.tags', null, ['label' => 'Labels', 'template' => 'admin/national_event/list_adherent_tags.html.twig'])
            ->add('postalCode', null, ['label' => 'Code postal'])
            ->add('details', null, [
                'label' => 'Détails',
                'virtual_field' => true,
                'template' => 'admin/national_event/list_details.html.twig',
                'header_style' => 'min-width: 300px;',
            ])
            ->add('status', 'trans', ['label' => 'Statut', 'header_style' => 'min-width: 160px;'])
            ->add('amount', null, ['label' => 'Montant', 'template' => 'admin/national_event/list_amount.html.twig'])
            ->add('paymentStatus', 'enum', ['label' => 'Statut du paiement', 'use_value' => true, 'enum_translation_domain' => 'messages', 'header_style' => 'min-width: 160px;'])
            ->add('ticketScannedAt', null, ['label' => 'Billet scanné le'])
            ->add('referrerCode', null, ['label' => 'Parrain', 'template' => 'admin/national_event/list_referrer_code.html.twig'])
            ->add('createdAt', null, ['label' => 'Inscrit le', 'header_style' => 'min-width: 140px;'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
            ->add('uuid', null, ['label' => 'Uuid', 'header_style' => 'min-width: 270px;'])
            ->add('publicId', null, ['label' => 'Public ID', 'header_style' => 'min-width: 90px;'])
            ->add('updatedAt', null, ['label' => 'Modifiée le', 'header_style' => 'min-width: 140px;'])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('gender', GenderCivilityType::class, ['label' => 'Civilité'])
                ->add('firstName', null, ['label' => 'Prénom'])
                ->add('lastName', null, ['label' => 'Nom'])
                ->add('postalCode', null, ['label' => 'Code postal'])
                ->add('birthdate', null, ['label' => 'Date de naissance', 'widget' => 'single_text'])
                ->add('birthPlace', null, ['label' => 'Lieu de naissance'])
                ->add('isJAM', null, ['label' => 'Jeunes avec Macron', 'required' => false])
                ->add('transportNeeds', null, ['label' => 'Besoin d\'un transport organisé', 'required' => false])
                ->add('volunteer', null, ['label' => 'Souhaite être bénévole pour aider à l\'organisation', 'required' => false])
                ->add('accessibility', TextareaType::class, ['label' => 'Handicap visible ou invisible', 'required' => false])
                ->add('qualities', QualityChoiceType::class, ['label' => 'Qualités', 'required' => false])
                ->add('phone', TelNumberType::class, ['label' => 'Téléphone', 'required' => false])
                ->add('children', TextareaType::class, ['label' => 'Enfant(s) accompagnant(s)', 'required' => false])
            ->end()
            ->with('Statut', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => InscriptionStatusEnum::STATUSES,
                    'choice_label' => fn (string $status) => $status,
                    'required' => true,
                ])
                ->add('validationComment', TextareaType::class, ['label' => 'Commentaire de validation', 'required' => false])
                ->add('validationStartedAt', null, ['label' => 'Date de début de validation', 'widget' => 'single_text', 'disabled' => true])
                ->add('validationFinishedAt', null, ['label' => 'Date de fin de validation', 'widget' => 'single_text', 'required' => false])
            ->end()
            ->with('Informations additionnelles', ['class' => 'col-md-6'])
                ->add('event', null, ['label' => 'Event', 'disabled' => true])
                ->add('uuid', null, ['label' => 'Uuid', 'disabled' => true])
                ->add('publicId', null, ['label' => 'Public ID', 'disabled' => true])
                ->add('addressEmail', null, ['label' => 'E-mail', 'disabled' => true])
                ->add('createdAt', null, ['label' => 'Inscrit le', 'widget' => 'single_text', 'disabled' => true])
                ->add('updatedAt', null, ['label' => 'Modifié le', 'widget' => 'single_text', 'disabled' => true])
                ->add('confirmedAt', null, ['label' => 'Présence confirmée le', 'widget' => 'single_text', 'disabled' => true])
                ->add('canceledAt', null, ['label' => 'Annulée le', 'widget' => 'single_text', 'disabled' => true])
                ->add('utmSource', null, ['label' => 'UTM Source', 'disabled' => true])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne', 'disabled' => true])
            ->end()
            ->with('Forfait', ['class' => 'col-md-6'])
                ->add('paymentStatus', ChoiceType::class, [
                    'label' => 'Statut du paiement',
                    'choices' => PaymentStatusEnum::all(),
                    'choice_label' => fn (PaymentStatusEnum $status) => $status,
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('visitDay', TextType::class, ['label' => 'Jour de visite', 'required' => false, 'disabled' => true])
                ->add('transport', TextType::class, ['label' => 'Choix de transport', 'required' => false, 'disabled' => true])
                ->add('accommodation', TextType::class, ['label' => 'Choix d\'hébergement', 'required' => false, 'disabled' => true])
                ->add('roommateIdentifier', TextType::class, ['label' => 'Numéro du partenaire', 'required' => false])
                ->add('amount', TextType::class, ['label' => 'Prix total (en centimes)', 'required' => false, 'disabled' => true])
                ->add('withDiscount', CheckboxType::class, ['label' => 'Bénéficie de -50%', 'required' => false, 'disabled' => true])
            ->end()
            ->with('Billet', ['class' => 'col-md-6'])
                ->add('ticketCustomDetail', null, ['label' => 'Champ libre (Porte A, Accès B, bracelet rouge, etc.)', 'required' => false])
                ->add('ticketSentAt', null, ['label' => 'Billet envoyé le', 'widget' => 'single_text', 'disabled' => true])
                ->add('ticketScannedAt', null, ['label' => 'Billet scanné le', 'widget' => 'single_text', 'disabled' => true])
            ->end()
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();
        $translator = $this->getTranslator();

        $departments = $this->zoneRepository->findAllDepartmentsIndexByCode();

        return [IteratorCallbackDataSource::CALLBACK => function (array $inscription) use ($translator, $departments) {
            /** @var EventInscription $inscription */
            $inscription = $inscription[0];
            $nationalEvent = $inscription->event;
            $adherent = $inscription->adherent;

            $code = substr($inscription->postalCode, 0, 2);

            $zone = $departments[$code] ?? null;

            return [
                'Région' => $zone['region_name'] ?? null,
                'Département' => $zone['name'] ?? null,
                'Événement national' => $nationalEvent->getName(),
                'Événement national UUID' => $nationalEvent->getUuid()->toString(),
                'Participant UUID' => $inscription->getUuid()->toString(),
                'Email' => $inscription->addressEmail,
                'PublicId' => $inscription->getPublicId(),
                'Civilité' => $inscription->gender ? $translator->trans(array_search($inscription->gender, Genders::CIVILITY_CHOICES, true)) : null,
                'Prénom' => $inscription->firstName,
                'Nom' => $inscription->lastName,
                'Labels Adhérent' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($adherent?->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ADHERENT) || str_starts_with($tag, TagEnum::SYMPATHISANT)))),
                'Labels Élu' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($adherent?->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ELU)))),
                'Labels Divers' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($adherent?->tags ?? [], fn (string $tag) => !str_starts_with($tag, TagEnum::ADHERENT) && !str_starts_with($tag, TagEnum::SYMPATHISANT) && !str_starts_with($tag, TagEnum::ELU)))),
                'Rôles' => implode(', ', array_map(function (AdherentZoneBasedRole $role) use ($translator): string {
                    return \sprintf(
                        '%s [%s]',
                        $translator->trans('role.'.$role->getType(), ['gender' => $role->getAdherent()->getGender()]),
                        implode(', ', array_map(function (Zone $zone): string {
                            return \sprintf(
                                '%s (%s)',
                                $zone->getName(),
                                $zone->getCode()
                            );
                        }, $role->getZones()->toArray()))
                    );
                }, $adherent?->getZoneBasedRoles() ?? [])),
                'Rôle délégué' => implode(', ', array_map(function (DelegatedAccess $delegatedAccess): string {
                    return $delegatedAccess->getRole();
                }, $adherent?->getReceivedDelegatedAccesses()->toArray() ?? [])),
                'Mandats' => implode(', ', array_map(function (ElectedRepresentativeAdherentMandate $mandate) use ($translator): string {
                    $str = $translator->trans('adherent.mandate.type.'.$mandate->mandateType);

                    if ($zone = $mandate->zone) {
                        $str .= \sprintf(
                            ' [%s (%s)]',
                            $zone->getName(),
                            $zone->getCode()
                        );
                    }

                    return $str;
                }, $adherent?->getElectedRepresentativeMandates() ?? [])),
                'Date de naissance' => $inscription->birthdate?->format('d/m/Y'),
                'Lieu de naissance' => $inscription->birthPlace,
                'Téléphone' => PhoneNumberUtils::format($inscription->phone),
                'Date d\'inscription' => $inscription->getCreatedAt()->format('d/m/Y H:i:s'),
                'Date de confirmation' => $inscription->confirmedAt?->format('d/m/Y H:i:s'),
                'Statut' => $translator->trans($inscription->status),
                'Billet envoyé le' => $inscription->ticketSentAt?->format('d/m/Y H:i:s'),
                'Billet champ libre' => $inscription->ticketCustomDetail,
                'Billet scanné le' => $inscription->ticketScannedAt?->format('d/m/Y H:i:s'),
                'Code postal' => $inscription->postalCode,
                'Qualités' => implode(', ', array_map(fn (string $quality) => QualityEnum::LABELS[$quality] ?? $quality, $inscription->qualities ?? [])),
                'Besoin d\'un transport organisé' => $inscription->transportNeeds ? 'Oui' : 'Non',
                'Souhaite être bénévole' => $inscription->volunteer ? 'Oui' : 'Non',
                'Handicap' => $inscription->accessibility,
                'Enfants' => $inscription->children,
                'JAM' => $inscription->isJAM ? 'Oui' : 'Non',
                'Jour de visite' => $inscription->visitDay,
                'Choix de transport' => $inscription->transport,
                'Choix d\'hébergement' => $inscription->accommodation,
                'Numéro du partenaire' => $inscription->roommateIdentifier,
                'Bénéficie de -50%' => true === $inscription->withDiscount ? 'Oui' : 'Non',
                'UTM source' => $inscription->utmSource,
                'UTM campagne' => $inscription->utmCampaign,
            ];
        }];
    }

    /** @param QueryBuilder|ProxyQueryInterface $query */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect(
                '_adherent',
                '_adherent_mandate',
                '_delegated_access',
                '_zone_based_role',
                '_zone_based_role_zone',
            )
            ->leftJoin("$alias.adherent", '_adherent')
            ->leftJoin('_adherent.adherentMandates', '_adherent_mandate')
            ->leftJoin('_adherent.receivedDelegatedAccesses', '_delegated_access')
            ->leftJoin('_adherent.zoneBasedRoles', '_zone_based_role')
            ->leftJoin('_zone_based_role.zones', '_zone_based_role_zone')
        ;

        return $query;
    }

    /** @param EventInscription $object */
    protected function postPersist(object $object): void
    {
        $this->dispatchChange($object);
    }

    /** @param EventInscription $object */
    protected function postUpdate(object $object): void
    {
        $this->dispatchChange($object);
    }

    /** @param EventInscription $object */
    protected function postRemove(object $object): void
    {
        $this->dispatchChange($object);
    }

    private function dispatchChange(EventInscription $eventInscription): void
    {
        $this->bus->dispatch(new NationalEventInscriptionChangeCommand($eventInscription->getUuid()));
    }
}

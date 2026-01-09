<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Admin\AbstractAdmin;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\ZoneableAdminInterface;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\Geo\Zone;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Form\ColorType;
use App\Form\GenderCivilityType;
use App\Form\NationalEvent\QualityChoiceType;
use App\Form\TelNumberType;
use App\Mailchimp\Synchronisation\Command\NationalEventInscriptionChangeCommand;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\PaymentStatusEnum;
use App\NationalEvent\QualityEnum;
use App\Query\Utils\MultiColumnsSearchHelper;
use App\Repository\Geo\ZoneRepository;
use App\Repository\NationalEvent\NationalEventRepository;
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
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NullFilter;
use Sonata\Form\Type\DateRangePickerType;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Messenger\MessageBusInterface;

class NationalEventInscriptionsAdmin extends AbstractAdmin implements ZoneableAdminInterface
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
        $collection
            ->clearExcept(['list', 'edit', 'export'])
            ->add('sendTicket', $this->getRouterIdParameter().'/send-ticket')
        ;
    }

    protected function getAccessMapping(): array
    {
        return [
            'sendTicket' => AdminPermissionMap::PERMISSION_EDIT,
        ];
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
            ->add('event', null, [
                'label' => 'Event',
                'show_filter' => true,
                'field_options' => [
                    'query_builder' => function (NationalEventRepository $er): QueryBuilder {
                        return $er
                            ->createQueryBuilder('e')
                            ->orderBy('e.startDate', 'DESC')
                        ;
                    },
                ],
            ])
            ->add('ticketUuid', null, ['label' => 'Uuid billet'])
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
            ->add('ticketSentAt', DateTimeRangeFilter::class, [
                'label' => 'Billets envoyés le',
                'field_type' => DateTimeRangePickerType::class,
            ])
            ->add('ticketAvailable', CallbackFilter::class, [
                'label' => 'Billet envoyé ?',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'Oui' => 1,
                        'Non' => 0,
                    ],
                    'multiple' => false,
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->getQueryBuilder()->andWhere($alias.'.ticketSentAt '.($value->getValue() ? 'IS NOT NULL' : 'IS NULL'));

                    return true;
                },
            ])
            ->add('visitDay', ChoiceFilter::class, [
                'label' => 'Jour de présence',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $qb = $this->getModelManager()->createQuery(EventInscription::class, 'i')
                            ->select('e.name, i.visitDay')
                            ->innerJoin('i.event', 'e')
                            ->where('i.visitDay IS NOT NULL')
                            ->groupBy('e.id, i.visitDay')
                        ;

                        $choices = [];
                        foreach ($qb->getQuery()->getScalarResult() as $row) {
                            $choices[$row['name'].' : '.$row['visitDay']] = $row['visitDay'];
                        }

                        ksort($choices);

                        return $choices;
                    }),
                ],
            ])
            ->add('transport', ChoiceFilter::class, [
                'label' => 'Forfait transport',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $qb = $this->getModelManager()->createQuery(EventInscription::class, 'i')
                            ->select('e.name, i.transport')
                            ->innerJoin('i.event', 'e')
                            ->where('i.transport IS NOT NULL')
                            ->groupBy('e.id, i.transport')
                        ;

                        $choices = [];
                        foreach ($qb->getQuery()->getScalarResult() as $row) {
                            $choices[$row['name'].' : '.$row['transport']] = $row['transport'];
                        }

                        ksort($choices);

                        return $choices;
                    }),
                ],
            ])
            ->add('accommodation', ChoiceFilter::class, [
                'label' => 'Forfait hébergement',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choice_loader' => new CallbackChoiceLoader(function () {
                        $qb = $this->getModelManager()->createQuery(EventInscription::class, 'i')
                            ->select('e.name, i.accommodation')
                            ->innerJoin('i.event', 'e')
                            ->where('i.accommodation IS NOT NULL')
                            ->groupBy('e.id, i.accommodation')
                        ;

                        $choices = [];
                        foreach ($qb->getQuery()->getScalarResult() as $row) {
                            $choices[$row['name'].' : '.$row['accommodation']] = $row['accommodation'];
                        }

                        ksort($choices);

                        return $choices;
                    }),
                ],
            ])
        ;

        if ($this->isGranted('ROLE_ADMIN_TERRITOIRES_NATIONAL_EVENTS_INSCRIPTIONS_ADVANCED')) {
            $filter
                ->add('firstTicketScannedAt', DateRangeFilter::class, [
                    'label' => 'Date de premier scan',
                    'field_type' => DateRangePickerType::class,
                ])
                ->add('lastTicketScannedAt', DateRangeFilter::class, [
                    'label' => 'Date de dernier scan',
                    'field_type' => DateRangePickerType::class,
                ])
            ;
        }
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
            ->add('subscriptionStatus', null, [
                'label' => 'Abonnement',
                'virtual_field' => true,
                'template' => 'admin/national_event/list_subscription_status.html.twig',
            ])
            ->add('details', null, [
                'label' => 'Détails',
                'virtual_field' => true,
                'template' => 'admin/national_event/list_details.html.twig',
                'header_style' => 'min-width: 300px;',
            ])
            ->add('status', 'trans', ['label' => 'Statut', 'header_style' => 'min-width: 160px;'])
            ->add('details_access', null, [
                'label' => 'Accès',
                'virtual_field' => true,
                'template' => 'admin/national_event/list_details_access.html.twig',
                'header_style' => 'min-width: 200px;',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
                'edit' => [],
                'inscription_page' => ['template' => 'admin/national_event/list_action_inscription_page.html.twig'],
                'send_ticket' => ['template' => 'admin/national_event/list_action_send_ticket.html.twig'],
            ]])
            ->add('payment', null, [
                'label' => 'Paiement',
                'virtual_field' => true,
                'template' => 'admin/national_event/list_payment.html.twig',
                'header_style' => 'min-width: 180px;',
            ])
            ->add('postalCode', null, ['label' => 'Code postal'])
            ->add('dates', null, [
                'label' => 'Dates',
                'virtual_field' => true,
                'template' => 'admin/national_event/list_dates.html.twig',
                'header_style' => 'min-width: 250px;',
            ])
            ->add('referrerCode', null, ['label' => 'Parrain', 'template' => 'admin/national_event/list_referrer_code.html.twig'])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var NationalEvent[] $packageEvents */
        $packageEvents = $this->getModelManager()->createQuery(NationalEvent::class, 'e')
            ->select('e')
            ->where('e.packageConfig IS NOT NULL')
            ->getQuery()
            ->getResult()
        ;

        /** @var NationalEvent|null $currentEvent */
        $currentEvent = $this->getSubject()?->event;

        $form
            ->tab('Inscription ❤️')
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
                    ->add('addressEmail', null, ['label' => 'E-mail'])
                    ->add('emergencyContactName', null, ['label' => 'Nom du contact d’urgence'])
                    ->add('emergencyContactPhone', TelNumberType::class, ['label' => 'Nom du contact d’urgence'])
                    ->add('createdAt', null, ['label' => 'Inscrit le', 'widget' => 'single_text', 'disabled' => true])
                    ->add('updatedAt', null, ['label' => 'Modifié le', 'widget' => 'single_text', 'disabled' => true])
                    ->add('confirmedAt', null, ['label' => 'Présence confirmée le', 'widget' => 'single_text', 'disabled' => true])
                    ->add('canceledAt', null, ['label' => 'Annulée le', 'widget' => 'single_text', 'disabled' => true])
                    ->add('utmSource', null, ['label' => 'UTM Source', 'disabled' => true])
                    ->add('utmCampaign', null, ['label' => 'UTM Campagne', 'disabled' => true])
                ->end()
            ->end()
            ->tab('Forfait ✅')
                ->with('', ['class' => 'col-md-6', 'description' => '⚠️ Attention, si vous modifiez le forfait en tant qu\'admin, le prix ne changera pas automatiquement et le statut ne changera pas non plus. Pour obliger une personne à repayer, il faut la passer "En attente de paiement"'])
                    ->add('paymentStatus', ChoiceType::class, [
                        'label' => 'Statut du paiement',
                        'choices' => PaymentStatusEnum::all(),
                        'choice_label' => fn (PaymentStatusEnum $status) => $status,
                        'required' => false,
                    ])
                    ->add('packagePlan', ChoiceType::class, [
                        'label' => 'Forfait',
                        'required' => $currentEvent && $currentEvent->isJEM(),
                        'choice_loader' => new CallbackChoiceLoader(function () use ($packageEvents) {
                            $choices = [];
                            foreach ($packageEvents as $event) {
                                foreach ($event->getPackagePlans()['options'] ?? [] as $config) {
                                    $choices[$event->getName().' : '.$config['titre']] = $config['id'];
                                }
                            }

                            ksort($choices);

                            return $choices;
                        }),
                    ])
                    ->add('visitDay', ChoiceType::class, [
                        'label' => 'Jour de visite',
                        'required' => $currentEvent && $currentEvent->isCampus(),
                        'choice_loader' => new CallbackChoiceLoader(function () use ($packageEvents) {
                            $choices = [];
                            foreach ($packageEvents as $event) {
                                foreach ($event->getVisitDays()['options'] ?? [] as $config) {
                                    $choices[$event->getName().' : '.$config['titre']] = $config['id'];
                                }
                            }

                            ksort($choices);

                            return $choices;
                        }),
                    ])
                    ->add('transport', ChoiceType::class, [
                        'label' => 'Choix de transport',
                        'required' => $currentEvent && $currentEvent->isPackageEventType(),
                        'choice_loader' => new CallbackChoiceLoader(function () use ($packageEvents) {
                            $choices = [];
                            foreach ($packageEvents as $event) {
                                foreach ($event->getTransports()['options'] ?? [] as $config) {
                                    $choices[$event->getName().' : '.$config['titre'].' ('.(!empty($config['montant']) ? $config['montant'].' €' : 'gratuit').')'] = $config['id'];
                                }
                            }

                            ksort($choices);

                            return $choices;
                        }),
                    ])
                    ->add('accommodation', ChoiceType::class, [
                        'label' => 'Choix d\'hébergement',
                        'required' => $currentEvent && $currentEvent->isCampus(),
                        'choice_loader' => new CallbackChoiceLoader(function () use ($packageEvents) {
                            $choices = [];
                            foreach ($packageEvents as $event) {
                                foreach ($event->getAccommodations()['options'] ?? [] as $config) {
                                    $choices[$event->getName().' : '.$config['titre'].' ('.(!empty($config['montant']) ? $config['montant'].' €' : 'gratuit').')'] = $config['id'];
                                }
                            }

                            ksort($choices);

                            return $choices;
                        }),
                    ])
                    ->add('packageCity', ChoiceType::class, [
                        'label' => 'Ville de départ',
                        'required' => $currentEvent && $currentEvent->isJEM(),
                        'choice_loader' => new CallbackChoiceLoader(function () use ($packageEvents) {
                            $choices = [];
                            foreach ($packageEvents as $event) {
                                foreach ($event->getPackageCities()['options'] ?? [] as $city) {
                                    $choices[$event->getName().' : '.$city] = $city;
                                }
                            }

                            ksort($choices);

                            return $choices;
                        }),
                    ])
                    ->add('packageDepartureTime', ChoiceType::class, [
                        'label' => 'Moment de départ',
                        'required' => $currentEvent && $currentEvent->isJEM(),
                        'choice_loader' => new CallbackChoiceLoader(function () use ($packageEvents) {
                            $choices = [];
                            foreach ($packageEvents as $event) {
                                foreach ($event->getPackageDepartureTimes()['options'] ?? [] as $departureTime) {
                                    $choices[$event->getName().' : '.$departureTime['titre']] = $departureTime['titre'];
                                }
                            }

                            ksort($choices);

                            return $choices;
                        }),
                    ])
                    ->add('roommateIdentifier', TextType::class, ['label' => 'Numéro du partenaire', 'required' => false])
                    ->add('amount', TextType::class, ['label' => 'Prix total (en centimes)', 'required' => false])
                    ->add('withDiscount', CheckboxType::class, ['label' => 'Bénéficie de -50%', 'required' => false])
                ->end()
            ->end()
            ->tab('Billet 🎟️')
                ->with('', ['class' => 'col-md-6'])
                    ->add('ticketUuid', null, ['label' => 'Uuid ticket', 'disabled' => true])
                    ->add('ticketCustomDetail', null, ['label' => 'Champ libre (Porte A, Accès B, bracelet rouge, etc.)', 'required' => false])
                    ->add('ticketBracelet', null, ['label' => 'Bracelet', 'required' => false])
                    ->add('ticketBraceletColor', ColorType::class, ['label' => 'Couleur du bracelet', 'required' => false])
                    ->add('ticketSentAt', null, ['label' => 'Billet envoyé le', 'widget' => 'single_text', 'disabled' => true])
                    ->add('firstTicketScannedAt', null, ['label' => 'Billet scanné le', 'widget' => 'single_text', 'disabled' => true])
                ->end()
            ->end()
            ->tab('Détails 📝')
                ->with('Détail du transport', ['class' => 'col-md-6'])
                    ->add('transportDetail', TextareaType::class, ['label' => false, 'required' => false, 'attr' => ['rows' => 5]])
                ->end()
                ->with('Détail d\'hébergement', ['class' => 'col-md-6'])
                    ->add('accommodationDetail', TextareaType::class, ['label' => false, 'required' => false, 'attr' => ['rows' => 5]])
                ->end()
                ->with('Détails additionnels', ['class' => 'col-md-6'])
                    ->add('customDetail', TextareaType::class, ['label' => false, 'required' => false, 'attr' => ['rows' => 5]])
                ->end()
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
                }, $adherent?->getReceivedDelegatedAccesses() ?? [])),
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
                'Contact urgence nom' => $inscription->emergencyContactName,
                'Contact urgence téléphone' => PhoneNumberUtils::format($inscription->emergencyContactPhone),
                'Date d\'inscription' => $inscription->getCreatedAt()->format('d/m/Y H:i:s'),
                'Date de confirmation' => $inscription->confirmedAt?->format('d/m/Y H:i:s'),
                'Statut' => $translator->trans($inscription->status),
                'Billet envoyé le' => $inscription->ticketSentAt?->format('d/m/Y H:i:s'),
                'Billet champ libre / Porte' => $inscription->ticketCustomDetail,
                'Label bracelet' => $inscription->ticketBracelet,
                'Couleur bracelet' => $inscription->ticketBraceletColor,
                'Billet scanné le' => $inscription->firstTicketScannedAt?->format('d/m/Y H:i:s'),
                'Code postal' => $inscription->postalCode,
                'Qualités' => implode(', ', array_map(fn (string $quality) => QualityEnum::LABELS[$quality] ?? $quality, $inscription->qualities ?? [])),
                'Besoin d\'un transport organisé' => $inscription->transportNeeds ? 'Oui' : 'Non',
                'Souhaite être bénévole' => $inscription->volunteer ? 'Oui' : 'Non',
                'Handicap' => $inscription->accessibility,
                'Enfants' => $inscription->children,
                'JAM' => $inscription->isJAM ? 'Oui' : 'Non',
                'Choix de forfait' => implode("\n", array_map(
                    static fn ($k, $v) => \sprintf('%s: %s', $k, $v),
                    array_keys($inscription->packageValues),
                    $inscription->packageValues
                )),
                'Transport info' => $inscription->transportDetail,
                'Hébergement info' => $inscription->accommodationDetail,
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

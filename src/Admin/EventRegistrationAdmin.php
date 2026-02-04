<?php

declare(strict_types=1);

namespace App\Admin;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Admin\Exporter\IterableCallbackDataSourceTrait;
use App\Admin\Exporter\IteratorCallbackDataSource;
use App\Admin\Filter\AdherentTagFilter;
use App\Admin\Filter\StaticAdherentTagFilter;
use App\Doctrine\Utils\MultiColumnsSearchHelper;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentZoneBasedRole;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Entity\Geo\Zone;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use App\ValueObject\Genders;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventRegistrationAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    protected LoggerInterface $logger;
    private TranslatorInterface $translator;
    private TagTranslator $tagTranslator;
    protected EventDispatcherInterface $dispatcher;

    public function __construct(
        LoggerInterface $logger,
        TranslatorInterface $translator,
        TagTranslator $tagTranslator,
        EventDispatcherInterface $dispatcher,
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->translator = $translator;
        $this->tagTranslator = $tagTranslator;
        $this->dispatcher = $dispatcher;
    }

    /** @param QueryBuilder|ProxyQueryInterface $query */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $alias = $query->getRootAliases()[0];

        $query
            ->addSelect('adherent', 'event')
            ->leftJoin("$alias.adherent", 'adherent')
            ->leftJoin("$alias.event", 'event')
        ;

        return $query;
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'export']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('_user', CallbackFilter::class, [
                'label' => 'Recherche d\'inscrit',
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
                            ['adherent.firstName', 'adherent.lastName'],
                            ['adherent.lastName', 'adherent.firstName'],
                            ['adherent.emailAddress', 'adherent.emailAddress'],
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.emailAddress", "$alias.emailAddress"],
                        ],
                        [
                            'adherent.phone',
                        ],
                        [
                            'adherent.id',
                            'adherent.uuid',
                            'adherent.publicId',
                        ]
                    );

                    return true;
                },
            ])
            ->add('adherent.tags_adherents', AdherentTagFilter::class, [
                'label' => 'Labels militants',
                'tags' => TagEnum::getAdherentTags(),
            ])
            ->add('adherent.tags_elected', AdherentTagFilter::class, [
                'label' => 'Labels élus',
                'tags' => TagEnum::getElectTags(),
            ])
            ->add('adherent.tags_static', StaticAdherentTagFilter::class, [
                'label' => 'Labels divers',
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date d\'inscription',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
            ->add('event', ModelFilter::class, [
                'label' => 'Événement',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'class' => Event::class,
                    'multiple' => true,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'name',
                    ],
                ],
            ])
            ->add('event.status', ChoiceFilter::class, [
                'label' => 'Statut événement',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => Event::STATUSES,
                    'choice_translation_domain' => 'forms',
                    'choice_label' => function (?string $choice) {
                        return $choice;
                    },
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('createdAt', null, [
                'label' => 'Date d\'inscription',
            ])
            ->add('_user', null, [
                'label' => 'Inscrit',
                'virtual_field' => true,
                'template' => 'admin/event_registration/list_user.html.twig',
            ])
            ->add('_user_labels', null, [
                'label' => 'Labels militant',
                'virtual_field' => true,
                'template' => 'admin/event_registration/list_user_labels.html.twig',
            ])
            ->add('event', null, [
                'label' => 'Événement',
                'virtual_field' => true,
                'template' => 'admin/event_registration/list_event.html.twig',
            ])
        ;
    }

    protected function configureExportFields(): array
    {
        PhpConfigurator::disableMemoryLimit();

        return [IteratorCallbackDataSource::CALLBACK => function (array $eventRegistration) {
            /** @var EventRegistration $eventRegistration */
            $eventRegistration = $eventRegistration[0];

            $adherent = $eventRegistration->getAdherent();
            $event = $eventRegistration->getEvent();

            try {
                return [
                    'UUID de participant' => $eventRegistration->getUuid()->toString(),
                    'Date d\'inscription' => $eventRegistration->getCreatedAt()?->format('d/m/Y H:i:s'),
                    'Numéro adhérent' => $adherent?->getPublicId(),
                    'Civilité' => $adherent ?
                        $this->translator->trans(array_search($adherent->getGender(), Genders::CIVILITY_CHOICES, true))
                        : null,
                    'Nom' => $eventRegistration->getLastName(),
                    'Prénom' => $eventRegistration->getFirstName(),
                    'Email' => $eventRegistration->getEmailAddress(),
                    'Téléphone' => PhoneNumberUtils::format($adherent?->getPhone()),
                    'Labels militants' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (!\in_array($tag, TagEnum::getAdherentTags(), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $adherent?->tags ?? []))),
                    'Labels Élus' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (!\in_array($tag, TagEnum::getElectTags(), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $adherent?->tags ?? []))),
                    'Labels divers' => implode(', ', array_filter(array_map(function (string $tag): ?string {
                        if (\in_array($tag, array_merge(TagEnum::getAdherentTags(), TagEnum::getElectTags()), true)) {
                            return null;
                        }

                        return $this->tagTranslator->trans($tag);
                    }, $adherent?->tags ?? []))),
                    'Mandats d\'élu' => implode(', ', array_map(function (ElectedRepresentativeAdherentMandate $mandate): string {
                        $str = $this->translator->trans('adherent.mandate.type.'.$mandate->mandateType);

                        if ($zone = $mandate->zone) {
                            $str .= \sprintf(
                                ' [%s (%s)]',
                                $zone->getName(),
                                $zone->getCode()
                            );
                        }

                        return $str;
                    }, $adherent?->getElectedRepresentativeMandates() ?? [])),
                    'Rôles' => implode(
                        ', ',
                        array_merge(
                            array_map(function (AdherentZoneBasedRole $role): string {
                                return \sprintf(
                                    '%s [%s]',
                                    $this->translator->trans('role.'.$role->getType(), ['gender' => $role->getAdherent()->getGender()]),
                                    implode(', ', array_map(function (Zone $zone): string {
                                        return \sprintf(
                                            '%s (%s)',
                                            $zone->getName(),
                                            $zone->getCode()
                                        );
                                    }, $role->getZones()->toArray()))
                                );
                            }, $adherent?->getZoneBasedRoles() ?? []),
                            array_filter([
                                $adherent?->isPresidentOfAgora() ? $this->translator->trans('role.agora_president', ['gender' => $adherent->getGender()]) : null,
                                $adherent?->isGeneralSecretaryOfAgora() ? $this->translator->trans('role.agora_general_secretary', ['gender' => $adherent->getGender()]) : null,
                            ])
                        )
                    ),
                    'Code postal' => $eventRegistration->getPostalCode(),
                    'UUID événement' => $event->getUuid()->toString(),
                    'Nom de l\'événement' => $event->getName(),
                    'Instance liée' => $event->getAuthorInstance(),
                    'Date de début' => $event->getBeginAt()?->format('d/m/Y H:i:s'),
                    'Date de fin' => $event->getFinishAt()?->format('d/m/Y H:i:s'),
                    'Lien de visio' => $event->getVisioUrl(),
                    'Lien de live' => $event->liveUrl,
                    'Région de l\'événement' => implode(', ', array_map(function (Zone $zone): string {
                        return \sprintf('%s (%s)', $zone->getName(), $zone->getCode());
                    }, $event->getParentZonesOfType(Zone::REGION))),
                    'Assemblée de l\'événement' => ($assemblyZone = $event->getAssemblyZone())
                        ? \sprintf('%s (%s)', $assemblyZone->getName(), $assemblyZone->getCode())
                        : null,
                ];
            } catch (\Exception $e) {
                $this->logger->error(
                    \sprintf('Error exporting EventRegistration with UUID: %s. (%s)', $eventRegistration->getUuid()->toString(), $e->getMessage()),
                    ['exception' => $e]
                );

                return [
                    'UUID de participant' => $eventRegistration->getUuid()->toString(),
                ];
            }
        }];
    }
}

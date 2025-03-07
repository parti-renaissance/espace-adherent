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
use App\NationalEvent\InscriptionStatusEnum;
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
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Service\Attribute\Required;

class NationalEventInscriptionsAdmin extends AbstractAdmin
{
    use IterableCallbackDataSourceTrait;

    private TagTranslator $tagTranslator;
    private ZoneRepository $zoneRepository;

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
                            ["$alias.addressEmail", "$alias.addressEmail"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                        ]
                    );

                    return true;
                },
            ])
            ->add('event', null, ['label' => 'Event', 'show_filter' => true])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine(InscriptionStatusEnum::toArray(), InscriptionStatusEnum::toArray()),
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('uuid', null, ['label' => 'Uuid'])
            ->add('event', null, ['label' => 'Event'])
            ->add('gender', null, ['label' => 'Civilité'])
            ->add('firstName', null, ['label' => 'Prénom'])
            ->add('lastName', null, ['label' => 'Nom'])
            ->add('addressEmail', null, ['label' => 'E-mail'])
            ->add('postalCode', null, ['label' => 'Code postal'])
            ->add('status', 'trans', ['label' => 'Statut'])
            ->add('adherent.tags', null, ['label' => 'Labels', 'template' => 'admin/national_event/list_adherent_tags.html.twig'])
            ->add('referrerCode', null, ['label' => 'Parrain', 'template' => 'admin/national_event/list_referrer_code.html.twig'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('status', ChoiceType::class, ['label' => 'Statut', 'choices' => array_combine(InscriptionStatusEnum::toArray(), InscriptionStatusEnum::toArray())])
                ->add('gender', GenderCivilityType::class, ['label' => 'Civilité'])
                ->add('firstName', null, ['label' => 'Prénom'])
                ->add('lastName', null, ['label' => 'Nom'])
                ->add('postalCode', null, ['label' => 'Code postal'])
                ->add('birthdate', null, ['label' => 'Date de naissance', 'widget' => 'single_text'])
                ->add('birthPlace', null, ['label' => 'Lieu de naissance'])
                ->add('transportNeeds', null, ['label' => 'Besoin d\'un transport organisé', 'required' => false])
                ->add('volunteer', null, ['label' => 'Souhaite être bénévole pour aider à l\'organisation', 'required' => false])
                ->add('accessibility', null, ['label' => 'Handicap visible ou invisible', 'required' => false])
                ->add('qualities', QualityChoiceType::class, ['label' => 'Qualités'])
                ->add('phone', TelNumberType::class, [
                    'required' => false,
                ])
                ->add('children', null, ['label' => 'Enfant(s) accompagnant(s)', 'required' => false])
            ->end()
            ->with('Informations additionnelles', ['class' => 'col-md-6'])
                ->add('event', null, ['label' => 'Event', 'disabled' => true])
                ->add('uuid', null, ['label' => 'Uuid', 'disabled' => true])
                ->add('addressEmail', null, ['label' => 'E-mail', 'disabled' => true])
                ->add('utmSource', null, ['label' => 'UTM Source', 'disabled' => true])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne', 'disabled' => true])
                ->add('ticketSentAt', null, ['label' => 'Date d\'envoi du billet', 'widget' => 'single_text', 'disabled' => true])
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
                'Civilité' => $inscription->gender ? $translator->trans(array_search($inscription->gender, Genders::CIVILITY_CHOICES, true)) : null,
                'Prénom' => $inscription->firstName,
                'Nom' => $inscription->lastName,
                'Labels Adhérent' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($adherent?->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ADHERENT) || str_starts_with($tag, TagEnum::SYMPATHISANT)))),
                'Labels Élu' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($adherent?->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ELU)))),
                'Labels Divers' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($adherent?->tags ?? [], fn (string $tag) => !str_starts_with($tag, TagEnum::ADHERENT) && !str_starts_with($tag, TagEnum::SYMPATHISANT) && !str_starts_with($tag, TagEnum::ELU)))),
                'Rôles' => implode(', ', array_map(function (AdherentZoneBasedRole $role) use ($translator): string {
                    return \sprintf(
                        '%s [%s]',
                        $translator->trans('role.'.$role->getType()),
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
                'Statut' => $translator->trans($inscription->status),
                'Billet envoyé le' => $inscription->ticketSentAt?->format('d/m/Y H:i:s'),
                'Code postal' => $inscription->postalCode,
                'Qualités' => implode(', ', array_map(fn (string $quality) => QualityEnum::LABELS[$quality] ?? $quality, $inscription->qualities ?? [])),
                'Besoin d\'un transport organisé' => $inscription->transportNeeds ? 'Oui' : 'Non',
                'Souhaite être bénévole' => $inscription->volunteer ? 'Oui' : 'Non',
                'Handicap' => $inscription->accessibility,
                'Enfants' => $inscription->children,
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

    #[Required]
    public function setTagTranslator(TagTranslator $tagTranslator): void
    {
        $this->tagTranslator = $tagTranslator;
    }

    #[Required]
    public function setZoneRepository(ZoneRepository $zoneRepository): void
    {
        $this->zoneRepository = $zoneRepository;
    }
}

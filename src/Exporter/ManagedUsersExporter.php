<?php

namespace App\Exporter;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Entity\Geo\Zone;
use App\Entity\Projection\ManagedUser;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use App\Scope\ScopeGeneratorResolver;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use Sonata\Exporter\Exporter as SonataExporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUsersExporter
{
    public function __construct(
        private readonly SonataExporter $exporter,
        private readonly ManagedUserRepository $repository,
        private readonly TagTranslator $tagTranslator,
        private readonly TranslatorInterface $translator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function getResponse(string $format, ManagedUsersFilter $filter): Response
    {
        PhpConfigurator::disableMemoryLimit();

        $scope = $this->scopeGeneratorResolver->generate();
        $scopeCode = $scope?->getCode();

        $emailSubscriptionType = $scopeCode && \array_key_exists($scopeCode, SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES)
            ? SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scopeCode]
            : null;

        return $this->exporter->getResponse(
            $format,
            \sprintf('adherents--%s.%s', date('d-m-Y--H-i'), $format),
            new IteratorCallbackSourceIterator(
                new \ArrayIterator($this->repository->getExportQueryBuilder($filter)->getResult()),
                function (ManagedUser $managedUser) use ($emailSubscriptionType) {
                    return [
                        'PID' => $managedUser->publicId,
                        'Civilité' => $managedUser->getCivilityLabel(),
                        'Prénom' => $managedUser->getFirstName(),
                        'Nom' => $managedUser->getLastName(),
                        'Date de naissance' => $managedUser->getBirthdate()?->format('d/m/Y'),
                        'Téléphone' => PhoneNumberUtils::format($managedUser->getPhone()),
                        'Comité' => $managedUser->getCommittee(),
                        'Circonscription' => ($managedUser->getZonesOfType(Zone::DISTRICT)[0] ?? null)?->getNameCode(),
                        'Rôles' => implode(', ', array_map(fn (array $role) => $this->translator->trans('role.'.$role['role']), $managedUser->getRolesAsArray())),
                        'Labels Adhérent' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($managedUser->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ADHERENT) || str_starts_with($tag, TagEnum::SYMPATHISANT)))),
                        'Labels Élu' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($managedUser->tags ?? [], fn (string $tag) => str_starts_with($tag, TagEnum::ELU)))),
                        'Déclaration de mandats' => implode(', ', $managedUser->getDeclaredMandates()),
                        'Mandats' => implode(', ', $managedUser->getMandates()),
                        'Labels Divers' => implode(', ', array_map([$this->tagTranslator, 'trans'], array_filter($managedUser->tags ?? [], fn (string $tag) => !str_starts_with($tag, TagEnum::ADHERENT) && !str_starts_with($tag, TagEnum::SYMPATHISANT) && !str_starts_with($tag, TagEnum::ELU)))),
                        'Date de création de compte' => $managedUser->getCreatedAt()?->format(\DateTimeInterface::RFC1123),
                        'Date de première cotisation' => $managedUser->firstMembershipDonation?->format(\DateTimeInterface::RFC1123),
                        'Date de dernière cotisation' => $managedUser->lastMembershipDonation?->format(\DateTimeInterface::RFC1123),
                        'Date de dernière connexion' => $managedUser->lastLoggedAt?->format(\DateTimeInterface::RFC1123),
                        'Adresse postale' => $managedUser->getAddress(),
                        'Code postal' => $managedUser->getPostalCode(),
                        'Code INSEE' => $managedUser->getCityCode(),
                        'Ville' => $managedUser->getCity(),
                        'Pays' => Countries::getName($managedUser->getCountry()),
                        'Abonné email' => $managedUser->isEmailSubscribed() && (
                            !$emailSubscriptionType
                            || \in_array($emailSubscriptionType, $managedUser->getSubscriptionTypes(), true)
                        ),
                        'Abonné SMS' => $managedUser->hasSmsSubscriptionType(),
                    ];
                }
            )
        );
    }
}

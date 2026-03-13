<?php

declare(strict_types=1);

namespace App\Exporter;

use App\Adherent\Tag\TagEnum;
use App\Adherent\Tag\TagTranslator;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use App\Scope\ScopeGeneratorResolver;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;
use App\Utils\PhpConfigurator;
use App\ValueObject\Genders;
use Sonata\Exporter\ExporterInterface;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUsersExporter
{
    public function __construct(
        private readonly ExporterInterface $exporter,
        private readonly ManagedUserRepository $repository,
        private readonly TagTranslator $tagTranslator,
        private readonly TranslatorInterface $translator,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
    ) {
    }

    public function getResponse(string $format, ManagedUsersFilter $filter, bool $isVox = false): Response
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
                $this->repository->iterateForExport($filter),
                function (array $row) use ($emailSubscriptionType, $isVox) {
                    return $this->buildExportRow($row, $emailSubscriptionType, $isVox);
                }
            )
        );
    }

    private function buildExportRow(array $row, ?string $emailSubscriptionType, bool $isVox): array
    {
        if ($isVox) {
            return [
                'UUID' => isset($row['adherentUuid']) ? (string) $row['adherentUuid'] : null,
                'PID' => $row['publicId'] ?? null,
                'Civilité' => $row['civility'] ?? $this->getCivilityLabel($row['gender'] ?? null),
                'Prénom' => $row['firstName'] ?? null,
                'Nom' => $row['lastName'] ?? null,
                'Âge' => $row['age'] ?? null,
                'Date de naissance' => isset($row['birthdate']) && $row['birthdate'] instanceof \DateTimeInterface
                    ? $row['birthdate']->format('d/m/Y')
                    : null,
                'Date de création de compte' => isset($row['createdAt']) && $row['createdAt'] instanceof \DateTimeInterface
                    ? $row['createdAt']->format('d/m/Y H:i')
                    : null,
                'Date de première cotisation' => isset($row['firstMembershipDonation']) && $row['firstMembershipDonation'] instanceof \DateTimeInterface
                    ? $row['firstMembershipDonation']->format('d/m/Y H:i')
                    : null,
                'Date de dernière activité' => isset($row['lastLoggedAt']) && $row['lastLoggedAt'] instanceof \DateTimeInterface
                    ? $row['lastLoggedAt']->format('d/m/Y H:i')
                    : null,
                'Labels Adhérent' => $this->extractLabelsFromJson($row['adherentTags'] ?? null),
                'Labels Statique' => $this->extractLabelsFromJson($row['staticTags'] ?? null),
                'Labels Élu' => $this->extractLabelsFromJson($row['electTags'] ?? null),
                'Rôles' => $this->formatRolesFromJson($row['roles'] ?? null, $row['gender'] ?? null),
                'Abonné email' => $this->getSubscriptionStatus($row['subscriptions'] ?? null, 'email'),
                'Abonné SMS' => $this->getSubscriptionStatus($row['subscriptions'] ?? null, 'sms'),
            ];
        }

        return [
            'PID' => $row['publicId'] ?? null,
            'Civilité' => $this->getCivilityLabel($row['gender'] ?? null),
            'Prénom' => $row['firstName'] ?? null,
            'Nom' => $row['lastName'] ?? null,
            'Date de naissance' => isset($row['birthdate']) && $row['birthdate'] instanceof \DateTimeInterface
                ? $row['birthdate']->format('d/m/Y')
                : null,
            'Téléphone' => PhoneNumberUtils::format($row['phone'] ?? null),
            'Comité' => $row['committee'] ?? null,
            'Rôles' => $this->formatRoles($row['roles'] ?? null, $row['gender'] ?? null),
            'Labels Adhérent' => $this->formatAdherentTags($row['tags'] ?? null),
            'Labels Élu' => $this->formatElectTags($row['tags'] ?? null),
            'Déclaration de mandats' => implode(', ', $row['declaredMandates'] ?? []),
            'Mandats' => implode(', ', $row['mandates'] ?? []),
            'Labels Divers' => $this->formatStaticTags($row['tags'] ?? null),
            'Date de création de compte' => isset($row['createdAt']) && $row['createdAt'] instanceof \DateTimeInterface
                ? $row['createdAt']->format('d/m/Y H:i')
                : null,
            'Date de première cotisation' => isset($row['firstMembershipDonation']) && $row['firstMembershipDonation'] instanceof \DateTimeInterface
                ? $row['firstMembershipDonation']->format('d/m/Y H:i')
                : null,
            'Date de dernière cotisation' => isset($row['lastMembershipDonation']) && $row['lastMembershipDonation'] instanceof \DateTimeInterface
                ? $row['lastMembershipDonation']->format('d/m/Y H:i')
                : null,
            'Date de dernière connexion' => isset($row['lastLoggedAt']) && $row['lastLoggedAt'] instanceof \DateTimeInterface
                ? $row['lastLoggedAt']->format('d/m/Y H:i')
                : null,
            'Adresse postale' => $row['address'] ?? null,
            'Code postal' => $row['postalCode'] ?? null,
            'Ville' => $row['city'] ?? null,
            'Pays' => isset($row['country']) ? Countries::getName($row['country']) : null,
            'Abonné email' => $this->isEmailSubscribed($row, $emailSubscriptionType),
            'Abonné SMS' => $this->hasSmsSubscription($row),
        ];
    }

    private function getCivilityLabel(?string $gender): string
    {
        return match ($gender) {
            Genders::MALE => 'M',
            Genders::FEMALE => 'Mme',
            default => '',
        };
    }

    private function formatRoles(?array $roles, ?string $gender): string
    {
        if (!$roles) {
            return '';
        }

        $formattedRoles = [];
        foreach ($roles as $role) {
            $roleKey = $role['code'] ?? '';
            if ($roleKey) {
                $formattedRoles[] = $this->translator->trans('role.'.$roleKey, ['gender' => $gender]);
            }
        }

        return implode(', ', $formattedRoles);
    }

    private function formatAdherentTags(?array $tags): string
    {
        if (!$tags) {
            return '';
        }

        $filteredTags = array_filter(
            $tags,
            function (string $tag) {
                return str_starts_with($tag, TagEnum::ADHERENT) || str_starts_with($tag, TagEnum::SYMPATHISANT);
            }
        );

        return implode(', ', array_map([$this->tagTranslator, 'trans'], $filteredTags));
    }

    private function formatElectTags(?array $tags): string
    {
        if (!$tags) {
            return '';
        }

        $filteredTags = array_filter(
            $tags,
            function (string $tag) {
                return str_starts_with($tag, TagEnum::ELU);
            }
        );

        return implode(', ', array_map([$this->tagTranslator, 'trans'], $filteredTags));
    }

    private function formatStaticTags(?array $tags): string
    {
        if (!$tags) {
            return '';
        }

        $filteredTags = array_filter(
            $tags,
            function (string $tag) {
                return !str_starts_with($tag, TagEnum::ADHERENT)
                    && !str_starts_with($tag, TagEnum::SYMPATHISANT)
                    && !str_starts_with($tag, TagEnum::ELU);
            }
        );

        return implode(', ', array_map([$this->tagTranslator, 'trans'], $filteredTags));
    }

    private function isEmailSubscribed(array $row, ?string $emailSubscriptionType): bool
    {
        $mailchimpStatus = $row['mailchimpStatus'] ?? null;
        $subscriptionTypes = $row['subscriptionTypes'] ?? [];

        $isSubscribed = ContactStatusEnum::SUBSCRIBED === $mailchimpStatus;

        if (!$isSubscribed) {
            return false;
        }

        if (!$emailSubscriptionType) {
            return true;
        }

        return \in_array($emailSubscriptionType, $subscriptionTypes, true);
    }

    private function hasSmsSubscription(array $row): bool
    {
        $phone = $row['phone'] ?? null;
        $subscriptionTypes = $row['subscriptionTypes'] ?? [];

        return $phone && \in_array(SubscriptionTypeEnum::MILITANT_ACTION_SMS, $subscriptionTypes, true);
    }

    private function extractLabelsFromJson(?array $tags): string
    {
        if (!$tags) {
            return '';
        }

        $labels = array_map(
            function (array $tag) {
                $code = $tag['code'] ?? '';

                return $code ? $this->tagTranslator->trans($code) : '';
            },
            $tags
        );

        return implode(', ', array_filter($labels));
    }

    private function formatRolesFromJson(?array $roles, ?string $gender): string
    {
        if (!$roles) {
            return '';
        }

        $labels = array_map(
            function (array $role) use ($gender) {
                $roleKey = $role['code'] ?? '';

                return $roleKey ? $this->translator->trans('role.'.$roleKey, ['gender' => $gender]) : '';
            },
            $roles
        );

        return implode(', ', array_filter($labels));
    }

    private function getSubscriptionStatus(?array $subscriptions, string $type): bool
    {
        if (!$subscriptions || !isset($subscriptions[$type])) {
            return false;
        }

        return (bool) ($subscriptions[$type]['subscribed'] ?? false);
    }
}

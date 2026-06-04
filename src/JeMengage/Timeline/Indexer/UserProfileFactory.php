<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\JeMengage\Timeline\UserScopeTargetResolver;

/**
 * Pure mapping Adherent -> UserProfile (no I/O), enriched to carry every targeting dimension the push
 * emits (DESIGN Decision 6) so the indexer can match include AND exclude on each. Reuses the same
 * Adherent accessors as the legacy Algolia clause builder. `national` is not emitted (broadcast resolved
 * indexer-side); civility/age/dates are nullable and omitted by the DTO when null.
 */
class UserProfileFactory
{
    public function __construct(private readonly UserScopeTargetResolver $scopeTargetResolver)
    {
    }

    public function create(Adherent $user): UserProfile
    {
        $membership = $user->getCommitteeMembership();

        $zones = [];
        foreach ($user->getDeepZones() as $zone) {
            if (($type = $zone->getType()) && ($code = $zone->getCode())) {
                $zones[] = $type.':'.$code;
            }
        }

        $agoras = [];
        foreach ($user->agoraMemberships as $agoraMembership) {
            if ($uuid = $agoraMembership->agora?->getUuid()) {
                $agoras[] = $uuid->toRfc4122();
            }
        }

        $mandateTypes = array_map(
            static function (ElectedRepresentativeAdherentMandate $mandate): string {
                return $mandate->mandateType;
            },
            $user->findElectedRepresentativeMandates(true),
        );

        return new UserProfile(
            $user->getId(),
            $user->tags ?? [],
            array_values(array_unique($zones)),
            $membership ? [$membership->getCommittee()->getUuid()->toRfc4122()] : [],
            array_values(array_unique($agoras)),
            array_values($mandateTypes),
            $user->getMandates() ?? [],
            null !== $membership ? 1 : 0,
            $this->scopeTargetResolver->resolve($user),
            $user->getGender(),
            $user->getAge(),
            $this->formatDate($user->getFirstMembershipDonation()),
            $this->formatDate($user->getLastMembershipDonation()),
            $this->formatDate($user->getRegisteredAt()),
        );
    }

    private function formatDate(?\DateTimeInterface $date): ?string
    {
        if (null === $date) {
            return null;
        }

        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');
    }
}

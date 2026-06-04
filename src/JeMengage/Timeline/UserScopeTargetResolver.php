<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline;

use App\Entity\Adherent;
use App\Scope\ScopeEnum;

/**
 * Resolves the scope_targets vocabulary of a user: the roles the user holds. Extracted verbatim from
 * GetTimelineFeedsController so both the legacy Algolia clause builder and the canary UserProfile read
 * from one source (DRY). Asymmetric with the push side: here the keys are the user's roles, matched by
 * the indexer against the roles an item targets.
 */
class UserScopeTargetResolver
{
    /**
     * Returns all scope_targets keys for the user:
     * - Direct roles: "{role}"
     * - Team memberships: "{role}:{member_role}" + "{role}:*" (wildcard)
     *
     * @return string[]
     */
    public function resolve(Adherent $user): array
    {
        $keys = [];

        // Direct zone-based roles
        foreach ($user->getZoneBasedRoles() as $zoneBasedRole) {
            if ($type = $zoneBasedRole->getType()) {
                $keys[] = $type;
            }
        }

        // Direct roles outside ZoneBasedRole
        if ($user->isAnimator()) {
            $keys[] = ScopeEnum::ANIMATOR;
        }
        if ($user->isPresidentOfAgora()) {
            $keys[] = ScopeEnum::AGORA_PRESIDENT;
        }
        if ($user->isGeneralSecretaryOfAgora()) {
            $keys[] = ScopeEnum::AGORA_GENERAL_SECRETARY;
        }
        if ($user->hasNationalRole()) {
            $keys[] = ScopeEnum::NATIONAL;
        }

        // Team memberships via delegated accesses
        foreach ($user->getReceivedDelegatedAccesses() as $delegatedAccess) {
            $type = $delegatedAccess->getType();
            $roleCode = $delegatedAccess->roleCode;

            if ($type && $roleCode) {
                $keys[] = $type.':'.$roleCode;
                $keys[] = $type.':*';
            }
        }

        return array_values(array_unique($keys));
    }
}

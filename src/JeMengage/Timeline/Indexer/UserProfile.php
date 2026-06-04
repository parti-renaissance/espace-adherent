<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\Indexer;

/**
 * Request body for the indexer POST /get_items (call-get-items.txt), enriched beyond the documented
 * subset so the indexer can match every targeting dimension the push emits as-is (DESIGN Decision 6):
 * adherent_ids (int), agoras, mandate_types, declared_mandates and all zone types — not only
 * region/department. Built server-side from the Adherent, never from user input.
 *
 * Keys are snake_case (the indexer contract). The always-present dimensions are sent even when empty
 * so the indexer applies include AND exclude on each. The nullable fields (civility, age, the three
 * membership/registration dates) are omitted when null: the indexer treats an absent field as "no
 * constraint" rather than receiving an empty/zero value. `national` is intentionally not a profile
 * field — an item flagged include.national:true is a broadcast resolved server-side by the indexer.
 */
class UserProfile implements \JsonSerializable
{
    /**
     * @param string[] $tags
     * @param string[] $zones
     * @param string[] $committees
     * @param string[] $agoras
     * @param string[] $mandateTypes
     * @param string[] $declaredMandates
     * @param string[] $scopeTargets
     */
    public function __construct(
        public readonly int $userId,
        public readonly array $tags,
        public readonly array $zones,
        public readonly array $committees,
        public readonly array $agoras,
        public readonly array $mandateTypes,
        public readonly array $declaredMandates,
        public readonly int $committeeMember,
        public readonly array $scopeTargets,
        public readonly ?string $civility = null,
        public readonly ?int $age = null,
        public readonly ?string $firstMembershipDate = null,
        public readonly ?string $lastMembershipDate = null,
        public readonly ?string $registeredDate = null,
    ) {
    }

    public function jsonSerialize(): array
    {
        $body = [
            'user_id' => (string) $this->userId,
            'tags' => $this->tags,
            'zones' => $this->zones,
            'committees' => $this->committees,
            'agoras' => $this->agoras,
            'mandate_types' => $this->mandateTypes,
            'declared_mandates' => $this->declaredMandates,
            'committee_member' => $this->committeeMember,
            'scope_targets' => $this->scopeTargets,
        ];

        if (null !== $this->civility) {
            $body['civility'] = $this->civility;
        }
        if (null !== $this->age) {
            $body['age'] = $this->age;
        }
        if (null !== $this->firstMembershipDate) {
            $body['first_membership_date'] = $this->firstMembershipDate;
        }
        if (null !== $this->lastMembershipDate) {
            $body['last_membership_date'] = $this->lastMembershipDate;
        }
        if (null !== $this->registeredDate) {
            $body['registered_date'] = $this->registeredDate;
        }

        return $body;
    }
}

<?php

declare(strict_types=1);

namespace App\Repository\Audience;

use App\AppSession\SessionStatusEnum;
use App\Donation\DonatorStatusEnum;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\Geo\Zone;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

/**
 * Translates an AdherentMessageFilter into the SQL fragments used by
 * AdherentRepository::countAdherentsForMessage / findAdherentIdsForMessage.
 *
 * The output shape (with / baseFrom / baseWhere / params / types /
 * branchEmailSql / branchPushSql) is consumed by the repository which
 * assembles the final COUNT or SELECT statement.
 */
class AdherentMessageAudiencePiecesBuilder
{
    private AdherentMessageFilter $filter;

    /** @var array<string, mixed> */
    private array $params;

    /** @var array<string, int> */
    private array $types;

    /** @var list<string> */
    private array $where;

    /** @var list<string> */
    private array $fromJoin;

    private string $with;
    private string $joinPerimeter;
    private string $scopeTargetJoin;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Builds the full SQL query to fetch FCM push token identifiers matching the
     * audience filter. Used by PushTokenRepository::findAllForAdherentMessage.
     *
     * Returns null if the audience is empty by design (same null conditions as build()).
     *
     * @param array{sql: string, params: array<string, mixed>, types: array<string, int>}|null $scopeTargetsQuery
     *
     * @return array{sql: string, params: array<string, mixed>, types: array<string, mixed>}|null
     */
    public function buildPushIdentifiersSql(AdherentMessage $message, ?array $scopeTargetsQuery): ?array
    {
        // byPush=null avoids generating the EXISTS branch — the push joins below replace it
        // with INNER JOINs that surface pt.identifier directly.
        $pieces = $this->build($message, byEmail: null, byPush: null, scopeTargetsQuery: $scopeTargetsQuery);
        if (null === $pieces) {
            return null;
        }

        $params = $pieces['params'];
        $types = $pieces['types'];
        $params['session_status'] = SessionStatusEnum::ACTIVE->value;

        $pushJoin = "\nJOIN app_session s ON s.adherent_id = a.id AND s.status = :session_status AND s.unsubscribed_at IS NULL"
            ."\nJOIN app_session_push_token_link link ON link.app_session_id = s.id AND link.unsubscribed_at IS NULL"
            ."\nJOIN push_token pt ON pt.id = link.push_token_id AND pt.unsubscribed_at IS NULL";

        $sql = ($pieces['with'] ? $pieces['with']."\n" : '')
            ."SELECT DISTINCT pt.identifier {$pieces['baseFrom']}{$pushJoin}\n"
            .'WHERE '.implode(' AND ', $pieces['baseWhere'])."\n"
            .'ORDER BY pt.identifier';

        return ['sql' => $sql, 'params' => $params, 'types' => $types];
    }

    /**
     * @param array{sql: string, params: array<string, mixed>, types: array<string, int>}|null $scopeTargetsQuery
     *
     * @return array{
     *     with: string,
     *     baseFrom: string,
     *     baseWhere: list<string>,
     *     params: array<string, mixed>,
     *     types: array<string, mixed>,
     *     branchEmailSql: ?string,
     *     branchPushSql: ?string,
     * }|null
     */
    public function build(AdherentMessage $message, ?bool $byEmail, ?bool $byPush, ?array $scopeTargetsQuery): ?array
    {
        $filter = $message->getFilter();

        if (!$filter instanceof AdherentMessageFilter) {
            return null;
        }

        $this->reset($filter);

        $managedZones = $filter->getZones()->toArray();

        if (!ScopeEnum::isNational($message->getInstanceScope()) && empty($managedZones) && !$filter->getCommittee()) {
            return null;
        }

        $this->applyManagedZonesPerimeter($managedZones);
        $this->applyFilterZone();
        $this->applyCommitteeMembership();
        $this->applyElectMandate();
        $this->applyDeclaredMandate();

        $this->where[] = 'a.status = :status_enabled';

        $this->applyGender();
        $this->applyAgeRange();
        $this->applyRegistrationDateRange();
        $this->applyMembershipDateRange();
        $this->applyTags();
        $this->applyCertification();
        $this->applyName();
        $this->applyPostalCode();
        $this->applyDonatorStatus();
        $this->applyScopeTargets($scopeTargetsQuery);

        $stId = $this->resolveSubscriptionTypeId();
        $branchEmailSql = $this->buildEmailBranch($byEmail, $stId);
        $branchPushSql = $this->buildPushBranch($byPush);

        return [
            'with' => $this->with,
            'baseFrom' => $this->buildBaseFrom(),
            'baseWhere' => $this->where,
            'params' => $this->params,
            'types' => $this->types,
            'branchEmailSql' => $branchEmailSql,
            'branchPushSql' => $branchPushSql,
        ];
    }

    private function reset(AdherentMessageFilter $filter): void
    {
        $this->filter = $filter;
        $this->params = [
            'status_enabled' => 'ENABLED',
            'mailchimp_subscribed' => 'subscribed',
        ];
        $this->types = [];
        $this->where = [];
        $this->fromJoin = [];
        $this->with = '';
        $this->joinPerimeter = '';
        $this->scopeTargetJoin = '';
    }

    /**
     * @param array<Zone> $managedZones
     */
    private function applyManagedZonesPerimeter(array $managedZones): void
    {
        if (empty($managedZones)) {
            return;
        }

        // Direct match: every zone is matched on adherent_zone.zone_id (no exception).
        // Parent match: only non-city-grouper zones cascade to children via geo_zone_parent.
        // City-grouper types (CANTON, DISTRICT) are assigned directly to adherents and must
        // not be expanded through their children. Aligns with GeoZoneTrait::createGeoZonesQueryBuilder.
        $directPlaceholders = [];
        $parentPlaceholders = [];
        $seenIds = [];

        foreach ($managedZones as $zone) {
            $id = $zone->getId();
            if (isset($seenIds[$id])) {
                continue;
            }
            $seenIds[$id] = true;

            $directKey = 'target_zone_'.\count($directPlaceholders);
            $directPlaceholders[] = ":$directKey";
            $this->params[$directKey] = $id;
            $this->types[$directKey] = ParameterType::INTEGER;

            if (!$zone->isCityGrouper()) {
                $parentKey = 'target_zone_parent_'.\count($parentPlaceholders);
                $parentPlaceholders[] = ":$parentKey";
                $this->params[$parentKey] = $id;
                $this->types[$parentKey] = ParameterType::INTEGER;
            }
        }

        $directInClause = implode(', ', $directPlaceholders);

        if (!empty($parentPlaceholders)) {
            $parentInClause = implode(', ', $parentPlaceholders);
            $this->with = <<<SQL
                    WITH z_adherents AS (
                        SELECT DISTINCT a.adherent_id
                        FROM adherent_zone a
                        LEFT JOIN geo_zone_parent p ON p.child_id = a.zone_id
                        WHERE p.parent_id IN ($parentInClause) OR a.zone_id IN ($directInClause)
                    )
                SQL;
        } else {
            // Every zone is a city-grouper → direct match only, no parent lookup needed.
            $this->with = <<<SQL
                    WITH z_adherents AS (
                        SELECT DISTINCT a.adherent_id
                        FROM adherent_zone a
                        WHERE a.zone_id IN ($directInClause)
                    )
                SQL;
        }

        $this->joinPerimeter = 'JOIN z_adherents za ON za.adherent_id = a.id';
    }

    private function applyFilterZone(): void
    {
        $filterZone = $this->filter->getZone();
        if (!$filterZone) {
            return;
        }

        if ($filterZone->isCityGrouper()) {
            // City-grouper zones (CANTON, DISTRICT) are direct-assigned, no parent lookup.
            $this->where[] = 'EXISTS (
                    SELECT 1
                    FROM adherent_zone az_filter
                    WHERE az_filter.adherent_id = a.id
                    AND az_filter.zone_id = :filter_zone_id
                )';
        } else {
            $this->where[] = 'EXISTS (
                    SELECT 1
                    FROM adherent_zone az_filter
                    LEFT JOIN geo_zone_parent p_filter ON p_filter.child_id = az_filter.zone_id
                    WHERE az_filter.adherent_id = a.id
                    AND (p_filter.parent_id = :filter_zone_id OR az_filter.zone_id = :filter_zone_id)
                )';
        }

        $this->params['filter_zone_id'] = $filterZone->getId();
        $this->types['filter_zone_id'] = ParameterType::INTEGER;
    }

    private function applyCommitteeMembership(): void
    {
        if (!$this->filter->getCommittee() && null === $this->filter->getIsCommitteeMember()) {
            return;
        }

        $this->fromJoin[] = 'LEFT JOIN committees_memberships cm ON cm.adherent_id = a.id';

        if ($this->filter->getCommittee()) {
            $this->where[] = 'cm.committee_id = :committee_id';
            $this->params['committee_id'] = $this->filter->getCommittee()->getId();
            $this->types['committee_id'] = ParameterType::INTEGER;
        }
        if (null !== $this->filter->getIsCommitteeMember()) {
            $this->where[] = 'cm.id '.($this->filter->getIsCommitteeMember() ? 'IS NOT NULL' : 'IS NULL');
        }
    }

    private function applyElectMandate(): void
    {
        $electMandate = $this->filter->getElectMandate();
        if (!$electMandate) {
            return;
        }

        $isExclude = str_starts_with($electMandate, '!');
        $mandateValue = ltrim($electMandate, '!');

        if ($isExclude) {
            $this->fromJoin[] = 'LEFT JOIN adherent_mandate am ON am.adherent_id = a.id AND am.type = :mandate_join_type AND am.mandate_type = :mandate_type AND am.finish_at IS NULL';
            $this->where[] = 'am.id IS NULL';
        } else {
            $this->fromJoin[] = 'JOIN adherent_mandate am ON am.adherent_id = a.id AND am.type = :mandate_join_type AND am.mandate_type = :mandate_type AND am.finish_at IS NULL';
        }

        $this->params['mandate_join_type'] = 'elected_representative';
        $this->params['mandate_type'] = $mandateValue;
    }

    private function applyDeclaredMandate(): void
    {
        $declaredMandate = $this->filter->getDeclaredMandate();
        if (!$declaredMandate) {
            return;
        }

        $isExclude = str_starts_with($declaredMandate, '!');
        $mandateValue = ltrim($declaredMandate, '!');

        if ($isExclude) {
            $this->where[] = '(a.mandates IS NULL OR FIND_IN_SET(:declared_mandate, a.mandates) = 0)';
        } else {
            $this->where[] = 'FIND_IN_SET(:declared_mandate, a.mandates) > 0';
        }
        $this->params['declared_mandate'] = $mandateValue;
    }

    private function applyGender(): void
    {
        if ($gender = $this->filter->getGender()) {
            $this->where[] = 'a.gender = :gender';
            $this->params['gender'] = $gender;
        }
    }

    private function applyAgeRange(): void
    {
        if (!$this->filter->getAgeMin() && !$this->filter->getAgeMax()) {
            return;
        }

        $now = new \DateTimeImmutable();

        if ($ageMin = $this->filter->getAgeMin()) {
            $this->where[] = 'a.birthdate <= :min_birth_date';
            $this->params['min_birth_date'] = $now->sub(new \DateInterval(\sprintf('P%dY', $ageMin)))->format('Y-m-d');
        }

        if ($ageMax = $this->filter->getAgeMax()) {
            $this->where[] = 'a.birthdate >= :max_birth_date';
            $this->params['max_birth_date'] = $now->sub(new \DateInterval(\sprintf('P%dY', $ageMax)))->format('Y-m-d');
        }
    }

    private function applyRegistrationDateRange(): void
    {
        if ($rs = $this->filter->getRegisteredSince()) {
            $this->where[] = 'a.registered_at >= :registered_since';
            $this->params['registered_since'] = $rs->format('Y-m-d 00:00:00');
        }
        if ($ru = $this->filter->getRegisteredUntil()) {
            $this->where[] = 'a.registered_at <= :registered_until';
            $this->params['registered_until'] = $ru->format('Y-m-d 23:59:59');
        }
    }

    private function applyMembershipDateRange(): void
    {
        if ($fs = $this->filter->firstMembershipSince) {
            $this->where[] = 'a.first_membership_donation >= :first_membership_since';
            $this->params['first_membership_since'] = $fs->format('Y-m-d 00:00:00');
        }
        if ($fb = $this->filter->firstMembershipBefore) {
            $this->where[] = 'a.first_membership_donation <= :first_membership_before';
            $this->params['first_membership_before'] = $fb->format('Y-m-d 23:59:59');
        }

        if ($ls = $this->filter->getLastMembershipSince()) {
            $this->where[] = 'a.last_membership_donation >= :last_membership_since';
            $this->params['last_membership_since'] = $ls->format('Y-m-d 00:00:00');
        }
        if ($lb = $this->filter->getLastMembershipBefore()) {
            $this->where[] = 'a.last_membership_donation <= :last_membership_before';
            $this->params['last_membership_before'] = $lb->format('Y-m-d 23:59:59');
        }
    }

    private function applyTags(): void
    {
        if ($tagPrefix = $this->filter->adherentTags) {
            $needle = ltrim($tagPrefix, '!');
            $this->where[] = 'a.tags '.(str_starts_with($tagPrefix, '!') ? 'NOT LIKE' : 'LIKE').' :tag_prefix';
            $this->params['tag_prefix'] = $needle.'%';
        }

        $idx = 0;
        foreach (array_filter([$this->filter->electTags, $this->filter->staticTags]) as $tag) {
            $needle = ltrim($tag, '!');
            $this->where[] = 'a.tags '.(str_starts_with($tag, '!') ? 'NOT LIKE' : 'LIKE')." :tag_contains_$idx";
            $this->params["tag_contains_$idx"] = '%'.$needle.'%';
            ++$idx;
        }
    }

    private function applyCertification(): void
    {
        if (null !== $isCertified = $this->filter->getIsCertified()) {
            $this->where[] = $isCertified ? 'a.certified_at IS NOT NULL' : 'a.certified_at IS NULL';
        }
    }

    private function applyName(): void
    {
        if (null !== $firstName = $this->filter->getFirstName()) {
            $this->where[] = 'a.first_name = :first_name';
            $this->params['first_name'] = $firstName;
        }

        if (null !== $lastName = $this->filter->getLastName()) {
            $this->where[] = 'a.last_name = :last_name';
            $this->params['last_name'] = $lastName;
        }
    }

    /**
     * Postal code filter: prefix LIKE on the embedded PostAddress (column address_postal_code,
     * see Adherent::$postAddress mapped with #[ORM\Embedded(columnPrefix: 'address_')]).
     * Aligned with AudienceFilterTrait DQL semantics ("postAddress.postalCode LIKE :postal_code%").
     */
    private function applyPostalCode(): void
    {
        if (null === $postalCode = $this->filter->postalCode) {
            return;
        }

        $this->where[] = 'a.address_postal_code LIKE :postal_code';
        $this->params['postal_code'] = $postalCode.'%';
    }

    private function applyDonatorStatus(): void
    {
        if (null === $donatorStatus = $this->filter->getDonatorStatus()) {
            return;
        }

        // Donator status is not denormalized: derived from first/last_membership_donation.
        // Aligned with DonatorStatusConditionBuilder (Mailchimp) which uses MERGE_FIELD_DONATION_YEARS push-side.
        $this->params['donator_current_year'] = (int) new \DateTimeImmutable()->format('Y');
        $this->types['donator_current_year'] = ParameterType::INTEGER;

        $this->where[] = match ($donatorStatus) {
            DonatorStatusEnum::DONATOR_N => 'YEAR(a.last_membership_donation) = :donator_current_year',
            DonatorStatusEnum::DONATOR_N_X => 'a.first_membership_donation IS NOT NULL AND (a.last_membership_donation IS NULL OR YEAR(a.last_membership_donation) < :donator_current_year)',
            DonatorStatusEnum::NOT_DONATOR => 'a.first_membership_donation IS NULL',
            default => '1 = 0',
        };
    }

    /**
     * @param array{sql: string, params: array<string, mixed>, types: array<string, int>}|null $scopeTargetsQuery
     */
    private function applyScopeTargets(?array $scopeTargetsQuery): void
    {
        if (null === $scopeTargetsQuery) {
            return;
        }

        foreach ($scopeTargetsQuery['params'] as $key => $value) {
            $this->params[$key] = $value;
        }
        foreach ($scopeTargetsQuery['types'] as $key => $type) {
            $this->types[$key] = $type;
        }

        $this->with .= ($this->with ? ",\n" : 'WITH ')."scope_target_adherents AS (\n".$scopeTargetsQuery['sql']."\n)";
        $this->scopeTargetJoin = 'JOIN scope_target_adherents sta ON sta.id = a.id';
    }

    private function resolveSubscriptionTypeId(): ?int
    {
        $scope = $this->filter->getScope();
        if (!$scope || empty(SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope])) {
            return null;
        }

        $stCode = SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[$scope];
        $stId = (int) $this->connection->fetchOne('SELECT id FROM subscription_type WHERE code = ?', [$stCode]);

        $this->params['st_id'] = $stId;
        $this->types['st_id'] = ParameterType::INTEGER;

        return $stId;
    }

    private function buildEmailBranch(?bool $byEmail, ?int $stId): ?string
    {
        if (null === $byEmail) {
            return null;
        }

        if ($byEmail) {
            $emailConds = ['a.mailchimp_status = :mailchimp_subscribed'];
            if ($stId) {
                $emailConds[] = 'EXISTS (
                        SELECT 1
                        FROM adherent_subscription_type ast
                        WHERE ast.adherent_id = a.id AND ast.subscription_type_id = :st_id
                    )';
            }

            return '('.implode(' AND ', $emailConds).')';
        }

        $emailConds = ['a.mailchimp_status <> :mailchimp_subscribed'];
        if ($stId) {
            $emailConds[] = 'NOT EXISTS (
                        SELECT 1
                        FROM adherent_subscription_type ast
                        WHERE ast.adherent_id = a.id AND ast.subscription_type_id = :st_id
                    )';
        }

        return '('.implode(' OR ', $emailConds).')';
    }

    private function buildPushBranch(?bool $byPush): ?string
    {
        if (null === $byPush) {
            return null;
        }

        $this->params['session_status'] = SessionStatusEnum::ACTIVE->value;

        return ($byPush ? '' : 'NOT ').'EXISTS (
                SELECT 1
                FROM app_session s
                JOIN app_session_push_token_link p ON p.app_session_id = s.id AND p.unsubscribed_at IS NULL
                JOIN push_token pt ON pt.id = p.push_token_id AND pt.unsubscribed_at IS NULL
                WHERE s.adherent_id = a.id AND s.status = :session_status
            )';
    }

    private function buildBaseFrom(): string
    {
        return 'FROM adherents a'
            .($this->joinPerimeter ? "\n".$this->joinPerimeter : '')
            .($this->scopeTargetJoin ? "\n".$this->scopeTargetJoin : '')
            .(!empty($this->fromJoin) ? "\n".implode("\n", $this->fromJoin) : '');
    }
}

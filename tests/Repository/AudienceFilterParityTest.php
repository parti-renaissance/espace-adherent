<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Adherent\Authorization\ZoneBasedRoleTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\PushToken;
use App\Repository\PushTokenRepository;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Ensures all audience filters exposed in Publications UI are actually applied
 * when selecting push tokens. Each test case activates a single filter and
 * verifies the generated DQL contains the expected clause.
 *
 * If a new filter is added to PublicationsFilterLayout but not implemented
 * in AudienceFilterTrait, add a test case here — it will fail until the
 * filter is implemented.
 */
#[Group('functional')]
class AudienceFilterParityTest extends AbstractKernelTestCase
{
    private ?PushTokenRepository $pushTokenRepository = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pushTokenRepository = $this->getRepository(PushToken::class);
    }

    protected function tearDown(): void
    {
        $this->pushTokenRepository = null;

        parent::tearDown();
    }

    #[DataProvider('provideFilterWithExpectedDql')]
    public function testAudienceFilterProducesDqlClause(callable $configureFilter, string $expectedDqlFragment): void
    {
        $filter = new AdherentMessageFilter();
        $filter->setScope(ScopeEnum::NATIONAL);
        $configureFilter($filter);

        $qb = $this->manager->createQueryBuilder()
            ->select('a')
            ->from(Adherent::class, 'a')
        ;

        $this->pushTokenRepository->applyAudienceFilter($filter, $qb, 'a');

        $dql = $qb->getDQL();

        self::assertStringContainsString(
            $expectedDqlFragment,
            $dql,
            \sprintf('Filter should produce DQL containing "%s". Got: %s', $expectedDqlFragment, $dql)
        );
    }

    public static function provideFilterWithExpectedDql(): iterable
    {
        yield 'gender' => [
            function (AdherentMessageFilter $f): void { $f->setGender('male'); },
            'a.gender = :gender',
        ];

        yield 'ageMin' => [
            function (AdherentMessageFilter $f): void { $f->setAgeMin(25); },
            'a.birthdate <= :min_birth_date',
        ];

        yield 'ageMax' => [
            function (AdherentMessageFilter $f): void { $f->setAgeMax(65); },
            'a.birthdate >= :max_birth_date',
        ];

        yield 'isCertified true' => [
            function (AdherentMessageFilter $f): void { $f->setIsCertified(true); },
            'a.certifiedAt IS NOT NULL',
        ];

        yield 'isCertified false' => [
            function (AdherentMessageFilter $f): void { $f->setIsCertified(false); },
            'a.certifiedAt IS NULL',
        ];

        yield 'postalCode' => [
            function (AdherentMessageFilter $f): void { $f->postalCode = '75'; },
            'a.postAddress.postalCode LIKE :postal_code',
        ];

        yield 'electMandate' => [
            function (AdherentMessageFilter $f): void { $f->setElectMandate('conseiller_municipal'); },
            'am_filter.mandateType = :mandate_type',
        ];

        yield 'electMandate NOT' => [
            function (AdherentMessageFilter $f): void { $f->setElectMandate('!conseiller_municipal'); },
            'am_filter.id IS NULL',
        ];

        yield 'declaredMandate' => [
            function (AdherentMessageFilter $f): void { $f->setDeclaredMandate('maire'); },
            'FIND_IN_SET(:declared_mandate, a.mandates) > 0',
        ];

        yield 'declaredMandate NOT' => [
            function (AdherentMessageFilter $f): void { $f->setDeclaredMandate('!maire'); },
            'FIND_IN_SET(:declared_mandate, a.mandates) = 0',
        ];

        yield 'isCommitteeMember true' => [
            function (AdherentMessageFilter $f): void { $f->setIsCommitteeMember(true); },
            'a.committeeMembership IS NOT NULL',
        ];

        yield 'isCommitteeMember false' => [
            function (AdherentMessageFilter $f): void { $f->setIsCommitteeMember(false); },
            'a.committeeMembership IS NULL',
        ];

        yield 'adherentTags' => [
            function (AdherentMessageFilter $f): void { $f->adherentTags = 'adherent'; },
            'a.tags LIKE :tag_adherent',
        ];

        yield 'adherentTags NOT' => [
            function (AdherentMessageFilter $f): void { $f->adherentTags = '!adherent'; },
            'a.tags NOT LIKE :tag_adherent',
        ];

        yield 'electTags' => [
            function (AdherentMessageFilter $f): void { $f->electTags = 'elect_tag'; },
            'a.tags LIKE :tag_0',
        ];

        yield 'electTags NOT' => [
            function (AdherentMessageFilter $f): void { $f->electTags = '!elect_tag'; },
            'a.tags NOT LIKE :tag_0',
        ];

        yield 'staticTags' => [
            function (AdherentMessageFilter $f): void { $f->staticTags = 'static_tag'; },
            'a.tags LIKE :tag_',
        ];

        yield 'staticTags NOT' => [
            function (AdherentMessageFilter $f): void { $f->staticTags = '!static_tag'; },
            'a.tags NOT LIKE :tag_',
        ];

        yield 'registeredSince' => [
            function (AdherentMessageFilter $f): void { $f->setRegistered(['start' => '2020-01-01', 'end' => null]); },
            'a.registeredAt >= :registered_since',
        ];

        yield 'registeredUntil' => [
            function (AdherentMessageFilter $f): void { $f->setRegistered(['start' => null, 'end' => '2025-01-01']); },
            'a.registeredAt <= :registered_until',
        ];

        yield 'firstMembershipSince' => [
            function (AdherentMessageFilter $f): void { $f->setFirstMembership(['start' => '2020-01-01', 'end' => null]); },
            'a.firstMembershipDonation >= :first_membership_since',
        ];

        yield 'lastMembershipBefore' => [
            function (AdherentMessageFilter $f): void { $f->setLastMembership(['start' => null, 'end' => '2025-01-01']); },
            'a.lastMembershipDonation <= :last_membership_before',
        ];

        yield 'scopeTargets' => [
            function (AdherentMessageFilter $f): void {
                $f->scopeTargets = [['role' => ZoneBasedRoleTypeEnum::ALL[0], 'include_role' => true, 'include_team' => false]];
            },
            'EXISTS (SELECT 1 FROM',
        ];
    }
}

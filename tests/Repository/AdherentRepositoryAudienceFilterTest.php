<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Adherent\MandateTypeEnum;
use App\DataFixtures\ORM\LoadAudienceFilterTestData;
use App\Donation\DonatorStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\Geo\Zone;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional tests for AdherentRepository::buildAudienceQueryPieces (Mailchimp email path).
 *
 * The dedicated fixtures (LoadAudienceFilterTestData) create orthogonal adherents,
 * each carrying a single discriminating attribute. Assertions check the inclusion/exclusion
 * of these adherents in the result of findAdherentIdsForMessage().
 *
 * Other fixture adherents (LoadAdherentData) remain in the database; the assertContains
 * and assertNotContains assertions tolerate their presence without enforcing a closed set.
 */
#[Group('functional')]
class AdherentRepositoryAudienceFilterTest extends AbstractKernelTestCase
{
    private ?AdherentRepository $adherentRepository = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        parent::tearDown();
    }

    public function testGenderFilterMaleIncludesMaleAdherentAndExcludesFemaleAdherent(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setGender('male');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_GENDER), $ids);
    }

    public function testGenderFilterFemaleIncludesFemaleAdherentAndExcludesMaleAdherent(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setGender('female');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_GENDER), $ids);
    }

    public function testGenderFilterNullDoesNotConstrainOnGender(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setGender(null);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_GENDER), $ids);
    }

    public function testAgeMinFilterIncludesAdherentsAtOrAboveTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setAgeMin(60);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testAgeMaxFilterIncludesAdherentsAtOrBelowTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setAgeMax(30);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
    }

    public function testRegisteredSinceFilterIncludesStartOfDay(): void
    {
        // registeredSince = 2023-01-01 must keep everything >= 2023-01-01 00:00:00.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setRegisteredSince(new \DateTime('2023-01-01'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_START), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_END), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2024), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2020), $ids);
    }

    public function testRegisteredUntilFilterIncludesEndOfDay(): void
    {
        // registeredUntil = 2023-01-01 must keep everything <= 2023-01-01 23:59:59.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setRegisteredUntil(new \DateTime('2023-01-01'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_START), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_END), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2020), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2024), $ids);
    }

    public function testFirstMembershipSinceFilterIncludesAdherentsWithDonationOnOrAfterTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->firstMembershipSince = new \DateTime('2022-01-01');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testFirstMembershipBeforeFilterIncludesAdherentsWithDonationOnOrBeforeTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->firstMembershipBefore = new \DateTime('2021-01-01');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testLastMembershipSinceFilterIncludesAdherentsWithDonationOnOrAfterTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setLastMembershipSince(new \DateTime('2025-01-01'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testLastMembershipBeforeFilterIncludesAdherentsWithDonationOnOrBeforeTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setLastMembershipBefore(new \DateTime('2024-12-31'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testAdherentTagsIncludeMatchesByPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // adherentTags = LIKE prefix sur a.tags
            $filter->adherentTags = 'adherent:renaissance';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testAdherentTagsExcludeWithBangPrefix(): void
    {
        // Note: NOT LIKE returns NULL for adherents with tags=NULL — they are therefore
        // excluded from the result. assertContains only targets adherents with non-empty tags.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->adherentTags = '!adherent:renaissance';
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testElectTagsIncludeMatchesByContains(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // electTags = LIKE contains sur a.tags
            $filter->electTags = 'elect:mayor';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testElectTagsExcludeWithBangPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->electTags = '!elect:mayor';
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testStaticTagsIncludeMatchesByContains(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // staticTags = LIKE contains sur a.tags
            $filter->staticTags = 'newsletter:abonne';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
    }

    public function testStaticTagsExcludeWithBangPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->staticTags = '!newsletter:abonne';
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
    }

    public function testDeclaredMandateIncludeMatchesFindInSet(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDeclaredMandate('maire');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DECLARED_MANDATE_MAIRE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testDeclaredMandateExcludeWithBangPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDeclaredMandate('!maire');
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DECLARED_MANDATE_MAIRE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCertifiedTrueIncludesCertifiedAdherentOnly(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCertified(true);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_CERTIFIED), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCertifiedFalseExcludesCertifiedAdherent(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCertified(false);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_CERTIFIED), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testFirstNameFilterMatchesByExactValue(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setFirstName(LoadAudienceFilterTestData::FIRST_NAME_CHARLES);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_NAME_CHARLES), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_NAME_SPECIAL), $ids);
    }

    public function testLastNameFilterMatchesByExactValue(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setLastName(LoadAudienceFilterTestData::LAST_NAME_SPECIAL);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_NAME_SPECIAL), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_NAME_CHARLES), $ids);
    }

    public function testDonatorStatusCurrentYearIncludesAdherentsWithDonationThisYear(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDonatorStatus(DonatorStatusEnum::DONATOR_N);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DONATOR_CURRENT_YEAR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testDonatorStatusNotDonatorIncludesAdherentsWithoutAnyDonation(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDonatorStatus(DonatorStatusEnum::NOT_DONATOR);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DONATOR_CURRENT_YEAR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
    }

    public function testDonatorStatusPastOnlyIncludesAdherentsDonatedBeforeCurrentYear(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDonatorStatus(DonatorStatusEnum::DONATOR_N_X);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DONATOR_CURRENT_YEAR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testElectMandateIncludeReturnsOnlyActiveMandates(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setElectMandate(MandateTypeEnum::CONSEILLER_MUNICIPAL);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_ACTIVE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_PAST), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testElectMandateExcludeRemovesAdherentsWithActiveMandateButKeepsExMandates(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setElectMandate('!'.MandateTypeEnum::CONSEILLER_MUNICIPAL);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_ACTIVE), $ids);
        // Expected semantics: a former representative (ended mandate) remains targetable.
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_PAST), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testHardBouncedAdherentIsExcludedFromEmailAudience(): void
    {
        // No email-specific filter: the default audience is all subscribed, non-bounced adherents.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_HARD_BOUNCED), $ids);
    }

    /**
     * Scenarios 1-7 cover the CTE `z_adherents` zone path (buildAudienceQueryPieces), the only filter
     * branch previously uncovered. They must stay green across the `OR` -> `UNION` rewrite: they are the
     * equivalence proof that the rewrite does not move the audience. Each scenario targets a dedicated
     * test zone (LoadAudienceFilterTestData), attached to this fixture's adherents only, so the audience
     * is isolated to the adherent under test.
     */
    public function testZoneTargetIncludesDirectlyAttachedAdherent(): void
    {
        $zone = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_DIRECT);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($zone): void {
            $filter->addZone($zone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_DIRECT), $ids);
        // Contrast with an adherent in a disjoint zone tree (child of another parent), never linked to this target.
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_CHILD_OF_PARENT), $ids);
    }

    public function testZoneParentTargetIncludesAdherentInChildZone(): void
    {
        $zone = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_PARENT);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($zone): void {
            $filter->addZone($zone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_CHILD_OF_PARENT), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_UNRELATED), $ids);
    }

    public function testZoneTargetExcludesAdherentInUnrelatedZone(): void
    {
        $target = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_DIRECT);
        $ownZone = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_UNRELATED);
        $unrelatedId = $this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_UNRELATED);

        $idsForTarget = $this->findIds(static function (AdherentMessageFilter $filter) use ($target): void {
            $filter->addZone($target);
        });
        $idsForOwnZone = $this->findIds(static function (AdherentMessageFilter $filter) use ($ownZone): void {
            $filter->addZone($ownZone);
        });

        // No over-inclusion: excluded from an unrelated target, yet reachable from its own zone —
        // proving the exclusion is about zone relatedness, not a globally unreachable adherent.
        $this->assertNotContains($unrelatedId, $idsForTarget);
        $this->assertContains($unrelatedId, $idsForOwnZone);
    }

    public function testZoneParentTargetCountsAdherentAttachedToBothParentAndChildOnce(): void
    {
        $zone = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_PARENT);

        $configure = static function (AdherentMessageFilter $filter) use ($zone): void {
            $filter->addZone($zone);
            // Pin the audience to the fan-out adherent: EMAIL_ZONE_CHILD_OF_PARENT also lands in this zone.
            $filter->setFirstName(LoadAudienceFilterTestData::FIRST_NAME_ZONE_FANOUT);
        };

        $ids = $this->findIds($configure);

        self::assertSame(
            [$this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_PARENT_AND_CHILD)],
            $ids,
            'an adherent matched by both the direct and the descent branch must appear once, not once per branch',
        );
    }

    public function testZonedAudienceCountMatchesIdListLength(): void
    {
        $zone = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_PARENT);

        $configure = static function (AdherentMessageFilter $filter) use ($zone): void {
            $filter->addZone($zone);
        };

        $ids = $this->findIds($configure);

        self::assertNotEmpty($ids, 'the zoned audience must not be empty, otherwise the equality is vacuous');
        self::assertSame(
            $this->adherentRepository->countAdherentsForMessage($this->buildMessage($configure), byEmail: true),
            \count($ids),
            'the zoned COUNT and id list must agree — the finalize compares them for strict equality',
        );
    }

    /**
     * Freezes the city-grouper status quo (see TECH_DEBT.md): the CTE descends from a CANTON target into
     * its child communes, whereas GeoZoneTrait::withGeoZones would not. This test acts the current behaviour,
     * it does not endorse it — settling direct-vs-descent for a city-grouper target is a product arbitration.
     * Only cantons carry children in the test fixture, so the district case (prod) stays untested by design.
     */
    public function testCantonTargetIncludesAdherentInChildCommune(): void
    {
        $zone = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_CANTON);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($zone): void {
            $filter->addZone($zone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_CANTON_CHILD), $ids);
    }

    public function testMultiParentZoneTargetCountsAdherentOnce(): void
    {
        $parentA = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_MULTI_PARENT_A);
        $parentB = $this->zoneOf(LoadAudienceFilterTestData::ZONE_CODE_MULTI_PARENT_B);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($parentA, $parentB): void {
            // The adherent's zone has both parents; both are targeted, so the descent branch matches twice.
            // UNION (like today's SELECT DISTINCT) dedups to one; UNION ALL would double it and break the finalize.
            $filter->addZone($parentA);
            $filter->addZone($parentB);
            $filter->setFirstName(LoadAudienceFilterTestData::FIRST_NAME_ZONE_MULTI_PARENT);
        });

        self::assertSame(
            [$this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_MULTI_PARENT)],
            $ids,
            'an adherent whose zone has several targeted parents must appear once, not once per parent',
        );
    }

    /**
     * The audience id list and the audience COUNT are built from the same query pieces, and the caller
     * relies on them agreeing: findAdherentIdsForMessage() feeds both expectedCount and the staged rows,
     * which insertIgnore then deduplicates on (adherent, static_segment) — while the finalize compares
     * the two for STRICT equality and blocks the campaign on any drift.
     *
     * The electMandate filter joins adherent_mandate on adherent + type + mandate_type + finish_at IS NULL,
     * and nothing in the schema keeps that set to one row: adherent_mandate carries no unique constraint
     * beyond its primary key. An adherent holding two active mandates of the same type — an old one whose
     * finish_at was never filled in, plus the current one — matches the join twice. COUNT(DISTINCT a.id)
     * sees one, the id list used to see two: the campaign would then block at finalize with
     * BlockReasonEnum::Empty, the very symptom of the 2026-07-16 incident from an unrelated cause.
     */
    public function testAdherentWithTwoActiveMandatesOfSameTypeIsListedOnce(): void
    {
        $adherent = $this->createAdherentWithTwoActiveMandates();

        $configure = static function (AdherentMessageFilter $filter) use ($adherent): void {
            $filter->setElectMandate(MandateTypeEnum::CONSEILLER_MUNICIPAL);
            // Pin the audience to this adherent alone: the shared test DB holds other mandate holders.
            $filter->setFirstName($adherent->getFirstName());
        };

        $ids = $this->findIds($configure);

        self::assertSame([$adherent->getId()], $ids, 'an adherent must appear once per audience, not once per mandate');
        self::assertSame(
            $this->adherentRepository->countAdherentsForMessage($this->buildMessage($configure), byEmail: true),
            \count($ids),
            'the id list and the COUNT must agree — the finalize compares them for strict equality',
        );
    }

    private function createAdherentWithTwoActiveMandates(): Adherent
    {
        $token = bin2hex(random_bytes(8));
        $firstName = 'FanOut'.substr($token, 0, 6);
        $email = \sprintf('audience-fanout-%s@test.dev', $token);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            substr($token, 0, 7), // public_id is varchar(7) UNIQUE
            $email,
            'super-password',
            'female',
            $firstName,
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );
        $this->manager->persist($adherent);

        // Two mandates of the same type, both open: the stale one nobody closed, and the current one.
        foreach (['2014-01-01', '2020-01-01'] as $beginAt) {
            $this->manager->persist(ElectedRepresentativeAdherentMandate::create(
                null,
                $adherent,
                MandateTypeEnum::CONSEILLER_MUNICIPAL,
                new \DateTime($beginAt),
                null,
            ));
        }

        $this->manager->flush();

        return $adherent;
    }

    /**
     * @return list<int>
     */
    private function findIds(callable $configure): array
    {
        return $this->adherentRepository->findAdherentIdsForMessage($this->buildMessage($configure));
    }

    private function buildMessage(callable $configure): AdherentMessage
    {
        $author = $this->adherentRepository->findOneByEmail(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG);
        $this->assertInstanceOf(Adherent::class, $author, 'LoadAudienceFilterTestData fixture must be loaded.');

        $message = AdherentMessage::createFromAdherent($author);
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $configure($filter);
        $message->setFilter($filter);

        return $message;
    }

    private function idOf(string $email): int
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);
        $this->assertInstanceOf(Adherent::class, $adherent, \sprintf('Fixture adherent "%s" not found.', $email));

        return $adherent->getId();
    }

    private function zoneOf(string $code): Zone
    {
        $zone = $this->manager->getRepository(Zone::class)->findOneBy(['code' => $code]);
        $this->assertInstanceOf(Zone::class, $zone, \sprintf('Fixture zone "%s" not found.', $code));

        return $zone;
    }
}

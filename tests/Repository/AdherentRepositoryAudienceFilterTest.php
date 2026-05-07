<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Adherent\MandateTypeEnum;
use App\DataFixtures\ORM\LoadAudienceFilterTestData;
use App\Donation\DonatorStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Geo\ZoneRepository;
use App\Scope\ScopeEnum;
use App\Subscription\SubscriptionTypeEnum;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Tests fonctionnels pour AdherentRepository::buildAudienceQueryPieces (path email Mailchimp).
 *
 * Les fixtures dédiées (LoadAudienceFilterTestData) créent des adhérents orthogonaux,
 * portant chacun un seul attribut discriminant. Les assertions vérifient l'inclusion/exclusion
 * de ces adhérents dans le résultat de findAdherentIdsForMessage().
 *
 * Les autres adhérents fixture (LoadAdherentData) restent présents en base ; les assertions
 * assertContains/assertNotContains tolèrent leur présence sans imposer un ensemble fermé.
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
        // registeredSince = 2023-01-01 doit retenir tout ce qui est >= 2023-01-01 00:00:00.
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
        // registeredUntil = 2023-01-01 doit retenir tout ce qui est <= 2023-01-01 23:59:59.
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
        // Note: NOT LIKE renvoie NULL pour les adhérents avec tags=NULL — ils sont donc exclus
        // du résultat. Les assertContains ciblent uniquement des adhérents avec tags non vides.
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
        // Sémantique attendue : un ex-élu (mandat terminé) reste ciblable.
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_PAST), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testCommitteeFilterReturnsOnlyMembersOfThatCommittee(): void
    {
        $committee = $this->getAudienceTestCommittee();

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($committee): void {
            $filter->setCommittee($committee);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_MEMBER), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_AUTHOR), $ids);
    }

    public function testIsCommitteeMemberTrueIncludesAdherentsWithMembership(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCommitteeMember(true);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_MEMBER), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCommitteeMemberFalseExcludesAdherentsWithMembership(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCommitteeMember(false);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_MEMBER), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testZoneFineGrainedFilterReturnsAdherentsInTargetZone(): void
    {
        $zone = $this->getAudienceTestZone();

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($zone): void {
            $filter->setZone($zone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_PARIS), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testManagedZonesFilterReturnsAdherentsInZonesPerimeter(): void
    {
        $zone = $this->getAudienceTestZone();

        $author = $this->adherentRepository->findOneByEmail(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG);
        self::assertInstanceOf(Adherent::class, $author);

        $message = AdherentMessage::createFromAdherent($author);
        // Scope non-national : la guard requiert alors zones managed ou committee.
        $message->setInstanceScope(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);

        $filter = new AdherentMessageFilter([$zone]);
        $message->setFilter($filter);

        $ids = $this->adherentRepository->findAdherentIdsForMessage($message);

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_PARIS), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testScopeSubscriptionTypeFilterReturnsAdherentsSubscribedToType(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // ScopeEnum::DEPUTY mappe vers SubscriptionTypeEnum::DEPUTY_EMAIL
            // (cf. SUBSCRIPTION_TYPES_BY_SCOPES).
            $filter->setScope(ScopeEnum::DEPUTY);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_SUBSCRIBED_DEPUTY), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        // Sanity : on n'a pas hardcodé une string magique, le mapping vient bien de l'enum.
        $this->assertSame(SubscriptionTypeEnum::DEPUTY_EMAIL, SubscriptionTypeEnum::SUBSCRIPTION_TYPES_BY_SCOPES[ScopeEnum::DEPUTY]);
    }

    private function getAudienceTestCommittee(): Committee
    {
        $committee = static::getContainer()->get(CommitteeRepository::class)->findOneByUuid(LoadAudienceFilterTestData::COMMITTEE_UUID);
        self::assertInstanceOf(Committee::class, $committee, 'Audience filter committee fixture not loaded.');

        return $committee;
    }

    private function getAudienceTestZone(): Zone
    {
        $zone = static::getContainer()->get(ZoneRepository::class)->findOneBy(['code' => '75056', 'type' => Zone::CITY]);
        self::assertInstanceOf(Zone::class, $zone, 'Expected zone city 75056 (Paris) to be loaded by LoadGeoZoneData.');

        return $zone;
    }

    /**
     * @return list<int>
     */
    private function findIds(callable $configure): array
    {
        $author = $this->adherentRepository->findOneByEmail(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG);
        $this->assertInstanceOf(Adherent::class, $author, 'LoadAudienceFilterTestData fixture must be loaded.');

        $message = AdherentMessage::createFromAdherent($author);
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $configure($filter);
        $message->setFilter($filter);

        return $this->adherentRepository->findAdherentIdsForMessage($message);
    }

    private function idOf(string $email): int
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);
        $this->assertInstanceOf(Adherent::class, $adherent, \sprintf('Fixture adherent "%s" not found.', $email));

        return $adherent->getId();
    }
}

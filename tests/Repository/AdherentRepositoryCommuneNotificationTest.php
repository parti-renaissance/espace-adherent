<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionTypeEnum;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional tests for AdherentRepository::findMembersAndAdherentsInZones (commune notification audience).
 *
 * Asserted against the shared LoadAdherentData set, anchored on commune 77288 which already gathers
 * every needed profile:
 *  - francis.brioul@yahoo.com         : adherent, event_email                       → adherent branch
 *  - commune-member-1@…              : sympathisant:membre, event_email             → member branch
 *  - coalitions-user-1@…              : sympathisant:adhesion_incomplete, event_email → excluded (only sympathisant:membre matches)
 *  - je-mengage-user-1@…             : adherent, NO event_email                     → preference filter
 *  - adherent-male-a@…               : plain user, event_email                      → tag filter
 *  - laura@deloche.com (76540)        : adherent, event_email, other commune         → zone filter
 */
#[Group('functional')]
class AdherentRepositoryCommuneNotificationTest extends AbstractKernelTestCase
{
    private const COMMUNE_CODE = '77288';

    private const ADHERENT_IN = 'francis.brioul@yahoo.com';
    private const MEMBER_IN = 'commune-member-1@en-marche-dev.fr';
    private const ADHESION_INCOMPLETE_OUT = 'coalitions-user-1@en-marche-dev.fr';
    private const ADHERENT_NO_SUB = 'je-mengage-user-1@en-marche-dev.fr';
    private const USER_IN = 'adherent-male-a@en-marche-dev.fr';
    private const ADHERENT_OTHER_ZONE = 'laura@deloche.com';

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

    public function testFindMembersAndAdherentsInZonesAppliesZonePreferenceAndTagFilters(): void
    {
        $emails = $this->emailsOf(
            $this->adherentRepository->findMembersAndAdherentsInZones([$this->commune()], SubscriptionTypeEnum::EVENT_EMAIL)
        );

        // Adherent and member (sympathisant:membre) of the commune, both subscribed to event_email.
        self::assertContains(self::ADHERENT_IN, $emails);
        self::assertContains(self::MEMBER_IN, $emails);

        // Excluded by the tag narrowing: an incomplete-adhesion sympathisant is not a sympathisant:membre,
        // even though it is in the commune and subscribed to event_email.
        self::assertNotContains(self::ADHESION_INCOMPLETE_OUT, $emails);
        // Excluded by the preference filter (no event_email subscription).
        self::assertNotContains(self::ADHERENT_NO_SUB, $emails);
        // Excluded by the tag filter (neither adherent nor member).
        self::assertNotContains(self::USER_IN, $emails);
        // Excluded by the zone filter (other commune).
        self::assertNotContains(self::ADHERENT_OTHER_ZONE, $emails);
    }

    public function testFindMembersAndAdherentsInZonesWithoutSubscriptionDoesNotConstrainOnPreference(): void
    {
        $emails = $this->emailsOf($this->adherentRepository->findMembersAndAdherentsInZones([$this->commune()]));

        // Without the subscription filter, the non-subscribed adherent is now included.
        self::assertContains(self::ADHERENT_NO_SUB, $emails);
        // The member (sympathisant:membre) is still matched.
        self::assertContains(self::MEMBER_IN, $emails);
        // The incomplete-adhesion sympathisant stays excluded: the tag narrowing is independent of the preference.
        self::assertNotContains(self::ADHESION_INCOMPLETE_OUT, $emails);
        // The tag and zone filters still apply.
        self::assertNotContains(self::USER_IN, $emails);
        self::assertNotContains(self::ADHERENT_OTHER_ZONE, $emails);
    }

    private function commune(): Zone
    {
        return $this->getRepository(Zone::class)->findOneBy(['code' => self::COMMUNE_CODE, 'type' => Zone::CITY]);
    }

    /**
     * @param Adherent[] $adherents
     *
     * @return string[]
     */
    private function emailsOf(array $adherents): array
    {
        return array_map(static fn (Adherent $adherent): string => $adherent->getEmailAddress(), $adherents);
    }
}

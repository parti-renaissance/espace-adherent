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
 *  - francis.brioul@yahoo.com         : adherent, event_email                 → adherent branch
 *  - coalitions-user-1@…              : member (sympathisant), event_email     → member branch
 *  - je-mengage-user-1@…             : adherent, NO event_email               → preference filter
 *  - adherent-male-a@…               : plain user, event_email                → tag filter
 *  - laura@deloche.com (76540)        : adherent, event_email, other commune   → zone filter
 */
#[Group('functional')]
class AdherentRepositoryCommuneNotificationTest extends AbstractKernelTestCase
{
    private const COMMUNE_CODE = '77288';

    private const ADHERENT_IN = 'francis.brioul@yahoo.com';
    private const MEMBER_IN = 'coalitions-user-1@en-marche-dev.fr';
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

        // Adherent and member of the commune, both subscribed to event_email.
        self::assertContains(self::ADHERENT_IN, $emails);
        self::assertContains(self::MEMBER_IN, $emails);

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
        // The member is still matched (sympathisant branch).
        self::assertContains(self::MEMBER_IN, $emails);
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

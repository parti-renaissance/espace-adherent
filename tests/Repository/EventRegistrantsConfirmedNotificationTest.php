<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Entity\Event\RegistrationStatusEnum;
use App\Entity\PushToken;
use App\JeMengage\Push\Command\NotifyEventRegistrantsCommand;
use App\Repository\EventRegistrationRepository;
use App\Repository\PushTokenRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional tests for the event update/cancel audience: confirmed registrants only, for both channels.
 *
 * Uses a committee event (LoadCommitteeEventData::EVENT_1) to prove the push registrants branch takes
 * priority over the committee-members branch. adherent-2 (Carl) and adherent-6 (Benjamin) are ENABLED
 * and carry an active push token ("token-adherent-2" / "token-adherent-6") via LoadAppSessionData, and
 * are not already registered to EVENT_1.
 */
#[Group('functional')]
class EventRegistrantsConfirmedNotificationTest extends AbstractKernelTestCase
{
    private ?Event $event = null;
    private ?Adherent $confirmed = null;
    private ?Adherent $invited = null;
    /** @var EventRegistration[] */
    private array $createdRegistrations = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = $this->getRepository(Event::class)->findOneBy(['uuid' => LoadCommitteeEventData::EVENT_1_UUID]);
        $this->confirmed = $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID);
        $this->invited = $this->getAdherent(LoadAdherentData::ADHERENT_6_UUID);

        $this->createdRegistrations = [
            $this->persistRegistration($this->confirmed, RegistrationStatusEnum::CONFIRMED),
            $this->persistRegistration($this->invited, RegistrationStatusEnum::INVITED),
        ];
        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdRegistrations as $registration) {
            if ($managed = $this->manager->find(EventRegistration::class, $registration->getId())) {
                $this->manager->remove($managed);
            }
        }
        $this->manager->flush();

        $this->event = $this->confirmed = $this->invited = null;
        $this->createdRegistrations = [];

        parent::tearDown();
    }

    public function testEventUpdateCancelAudienceTargetsConfirmedRegistrantsOnlyForBothChannels(): void
    {
        // --- Mail channel: findByEvent(CONFIRMED) excludes invited registrants ---
        /** @var EventRegistrationRepository $registrationRepository */
        $registrationRepository = $this->getEventRegistrationRepository();

        $confirmedEmails = $this->emailsOf($registrationRepository->findByEvent($this->event, RegistrationStatusEnum::CONFIRMED)->toArray());
        $allEmails = $this->emailsOf($registrationRepository->findByEvent($this->event)->toArray());

        self::assertContains($this->confirmed->getEmailAddress(), $confirmedEmails);
        self::assertNotContains($this->invited->getEmailAddress(), $confirmedEmails);
        // Without the status filter the invited registrant is included: proves the filter is what excludes them.
        self::assertContains($this->invited->getEmailAddress(), $allEmails);

        // --- Push channel: registrants branch returns confirmed registrants' tokens, even for a committee event ---
        /** @var PushTokenRepository $pushTokenRepository */
        $pushTokenRepository = $this->getRepository(PushToken::class);

        $tokens = $pushTokenRepository->findAllForNotificationObject(
            $this->event,
            new NotifyEventRegistrantsCommand($this->event->getUuid(), NotifyEventRegistrantsCommand::EVENT_CANCEL)
        );

        self::assertContains('token-adherent-2', $tokens);
        self::assertNotContains('token-adherent-6', $tokens);
    }

    private function persistRegistration(Adherent $adherent, RegistrationStatusEnum $status): EventRegistration
    {
        $registration = new EventRegistration(
            Uuid::v4(),
            $this->event,
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getEmailAddress(),
            null,
            false,
            $adherent,
            null,
            'now',
            $status,
        );

        $this->manager->persist($registration);

        return $registration;
    }

    /**
     * @param EventRegistration[] $registrations
     *
     * @return string[]
     */
    private function emailsOf(array $registrations): array
    {
        return array_map(static fn (EventRegistration $registration): string => $registration->getEmailAddress(), $registrations);
    }
}

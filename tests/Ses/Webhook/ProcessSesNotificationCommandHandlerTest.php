<?php

declare(strict_types=1);

namespace Tests\App\Ses\Webhook;

use App\Entity\Adherent;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentRepository;
use App\Ses\Webhook\Command\ProcessSesNotificationCommand;
use App\Ses\Webhook\Handler\ProcessSesNotificationCommandHandler;
use App\Ses\Webhook\SesNotificationParser;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class ProcessSesNotificationCommandHandlerTest extends AbstractKernelTestCase
{
    private int $seq = 0;

    public function testHardBounceFlagsRecipientWithoutTouchingConsent(): void
    {
        $adherent = $this->createSubscribedAdherent();
        $this->manager->flush();
        $email = $adherent->getEmailAddress();

        $this->handler()(new ProcessSesNotificationCommand($this->bounce($email)));

        $this->manager->clear();
        $reloaded = $this->getRepository(Adherent::class)->findOneByEmail($email);
        self::assertTrue($reloaded->isEmailHardBounced());
        // Technical suppression: the consent status is left untouched.
        self::assertSame(ContactStatusEnum::SUBSCRIBED, $reloaded->getMailchimpStatus());
        // A bounce is not a complaint.
        self::assertFalse($reloaded->isEmailComplained());
    }

    public function testComplaintUnsubscribesDurably(): void
    {
        $adherent = $this->createSubscribedAdherent();
        $this->manager->flush();
        $email = $adherent->getEmailAddress();

        $this->handler()(new ProcessSesNotificationCommand($this->complaint($email)));

        $this->manager->clear();
        $reloaded = $this->getRepository(Adherent::class)->findOneByEmail($email);
        self::assertSame(ContactStatusEnum::UNSUBSCRIBED, $reloaded->getMailchimpStatus());
        // The complaint is recorded as such, distinct from a voluntary unsubscribe.
        self::assertTrue($reloaded->isEmailComplained());
        // unsubscribeRequestedAt is the freeze guard that makes the unsubscribe survive a Mailchimp re-sync.
        self::assertNotNull($reloaded->unsubscribeRequestedAt);
        self::assertFalse($reloaded->isEmailHardBounced());
    }

    public function testUnknownRecipientIsIgnored(): void
    {
        $this->handler()(new ProcessSesNotificationCommand($this->bounce('nobody-here@example.org')));

        $this->addToAssertionCount(1); // reaching this line without exception is the assertion
    }

    public function testHardBounceIsIdempotent(): void
    {
        $adherent = $this->createSubscribedAdherent();
        $this->manager->flush();
        $email = $adherent->getEmailAddress();

        $handler = $this->handler();
        $handler(new ProcessSesNotificationCommand($this->bounce($email)));
        $this->manager->clear();
        $first = $this->getRepository(Adherent::class)->findOneByEmail($email)->emailHardBouncedAt;

        $handler(new ProcessSesNotificationCommand($this->bounce($email)));
        $this->manager->clear();
        $second = $this->getRepository(Adherent::class)->findOneByEmail($email)->emailHardBouncedAt;

        self::assertEquals($first, $second, 'replay keeps the first bounce date');
    }

    public function testUnparsableFeedbackIsLoggedAsError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $payload = ['MessageId' => 'sns-drift', 'Message' => json_encode([
            'eventType' => 'Bounce',
            'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => []],
        ])];

        $this->handler($logger)(new ProcessSesNotificationCommand($payload));
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function handler(?LoggerInterface $logger = null): ProcessSesNotificationCommandHandler
    {
        return new ProcessSesNotificationCommandHandler(
            self::getContainer()->get(SesNotificationParser::class),
            self::getContainer()->get(AdherentRepository::class),
            self::getContainer()->get(SubscriptionHandler::class),
            self::getContainer()->get(EntityManagerInterface::class),
            $logger ?? new NullLogger(),
        );
    }

    private function bounce(string $email): array
    {
        return ['MessageId' => 'sns-b', 'Message' => json_encode([
            'eventType' => 'Bounce',
            'bounce' => ['bounceType' => 'Permanent', 'bouncedRecipients' => [['emailAddress' => $email]]],
        ])];
    }

    private function complaint(string $email): array
    {
        return ['MessageId' => 'sns-c', 'Message' => json_encode([
            'eventType' => 'Complaint',
            'complaint' => ['complainedRecipients' => [['emailAddress' => $email]]],
        ])];
    }

    private function createSubscribedAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-webhook-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SW-%d', $seq),
            $email,
            'super-password',
            'female',
            'Alice',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );

        $this->manager->persist($adherent);

        return $adherent;
    }
}

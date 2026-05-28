<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Mailer\MailerService;
use App\Membership\AdherentFactory;
use App\Membership\MembershipNotifier;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use App\Membership\Signup\SignupCommand;
use App\Membership\Signup\SignupHandler;
use App\Membership\UserEvents;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use App\Repository\AdherentRepository;
use App\Repository\BannedAdherentRepository;
use App\Subscription\SubscriptionHandler;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\App\AbstractWebTestCase;

#[Group('functional')]
class SignupHandlerFunctionalTest extends AbstractWebTestCase
{
    public function testHandleNewEmailDispatchesConfirmationCommandWithoutUserCreated(): void
    {
        $dispatched = false;
        static::getContainer()->get('event_dispatcher')->addListener(
            UserEvents::USER_CREATED,
            static function () use (&$dispatched): void {
                $dispatched = true;
            }
        );

        $email = 'fresh-signup@example.test';

        $handler = new SignupHandler(
            $this->manager,
            static::getContainer()->get('doctrine'),
            static::getContainer()->get(AdherentRepository::class),
            static::getContainer()->get(BannedAdherentRepository::class),
            static::getContainer()->get(AdherentFactory::class),
            static::getContainer()->get(SubscriptionHandler::class),
            static::getContainer()->get(MembershipNotifier::class),
            static::getContainer()->get(MailerService::class),
            static::getContainer()->get(MessageBusInterface::class),
        );

        $handler->handle(new SignupCommand($email, 'newsletter'));

        // The lightweight signup must NOT trigger member onboarding (no zones / tags / Mailchimp member).
        self::assertFalse($dispatched, 'A lightweight signup must not dispatch USER_CREATED.');

        // A real PENDING contact has been registered with its source recorded.
        $persisted = static::getContainer()->get(AdherentRepository::class)->findOneByEmail($email);
        self::assertNotNull($persisted);
        self::assertTrue($persisted->isPending());
        self::assertNotNull(
            $this->manager->getRepository(AdherentSignupSource::class)->findOneBy([
                'adherent' => $persisted,
                'source' => 'newsletter',
            ])
        );

        // The confirmation command must reach the bus so the magic-link email is sent (test env routes to sync,
        // but the dispatch itself is recorded by RecorderMiddleware regardless of transport).
        $recorder = static::getContainer()->get(MessageRecorderInterface::class);
        $confirmationDispatched = false;
        foreach ($recorder->getMessages() as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof SendSignupConfirmationCommand
                && $message->adherent->getEmailAddress() === $email
            ) {
                $confirmationDispatched = true;
                break;
            }
        }
        self::assertTrue($confirmationDispatched, 'SendSignupConfirmationCommand must be dispatched on the bus.');
    }

    public function testHandleExistingPendingReDispatchesConfirmationInsteadOfConnexionDetails(): void
    {
        $email = 'pending-resignup@example.test';

        $handler = new SignupHandler(
            $this->manager,
            static::getContainer()->get('doctrine'),
            static::getContainer()->get(AdherentRepository::class),
            static::getContainer()->get(BannedAdherentRepository::class),
            static::getContainer()->get(AdherentFactory::class),
            static::getContainer()->get(SubscriptionHandler::class),
            static::getContainer()->get(MembershipNotifier::class),
            static::getContainer()->get(MailerService::class),
            static::getContainer()->get(MessageBusInterface::class),
        );

        // First call creates the PENDING account; second call simulates the user re-submitting
        // the signup form because they never received / confirmed the first code.
        $handler->handle(new SignupCommand($email, 'newsletter'));

        $persisted = static::getContainer()->get(AdherentRepository::class)->findOneByEmail($email);
        self::assertNotNull($persisted);
        self::assertSame(Adherent::PENDING, $persisted->getStatus());

        $recorder = static::getContainer()->get(MessageRecorderInterface::class);
        $beforeSecondCall = \count($recorder->getMessages());

        $handler->handle(new SignupCommand($email, 'newsletter'));

        // A new confirmation command must reach the bus on the second submission. Old behavior
        // (sendConnexionDetailsMessage) bypasses the bus and would leave this count at zero.
        $confirmationsAfter = 0;
        foreach (\array_slice($recorder->getMessages(), $beforeSecondCall) as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof SendSignupConfirmationCommand && $message->adherent->getEmailAddress() === $email) {
                ++$confirmationsAfter;
            }
        }

        self::assertSame(1, $confirmationsAfter, 'A re-signup on a PENDING account must dispatch a fresh SendSignupConfirmationCommand.');
    }
}

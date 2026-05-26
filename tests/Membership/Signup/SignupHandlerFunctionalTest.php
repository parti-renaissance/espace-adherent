<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup;

use App\Entity\AdherentSignupSource;
use App\Membership\AdherentFactory;
use App\Membership\Signup\SignupCommand;
use App\Membership\Signup\SignupHandler;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionHandler;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractWebTestCase;

#[Group('functional')]
class SignupHandlerFunctionalTest extends AbstractWebTestCase
{
    public function testRegisterDoesNotDispatchUserCreated(): void
    {
        $dispatched = false;
        static::getContainer()->get('event_dispatcher')->addListener(
            UserEvents::USER_CREATED,
            static function () use (&$dispatched): void {
                $dispatched = true;
            }
        );

        $email = 'fresh-signup@example.test';

        // The handler has no consumer yet (the endpoint lands in phase 3), so the compiler inlines it
        // out of the container. Build it from real services to exercise the genuine persistence path.
        $handler = new SignupHandler(
            $this->manager,
            static::getContainer()->get('doctrine'),
            static::getContainer()->get(AdherentRepository::class),
            static::getContainer()->get(AdherentFactory::class),
            static::getContainer()->get(SubscriptionHandler::class),
        );
        $adherent = $handler->register(new SignupCommand($email, 'newsletter'));

        // The lightweight signup must NOT trigger member onboarding (no zones / tags / Mailchimp member).
        self::assertFalse($dispatched, 'A lightweight signup must not dispatch USER_CREATED.');
        self::assertTrue($adherent->isPending());

        // A real PENDING contact has been registered with its source recorded.
        $persisted = static::getContainer()->get(AdherentRepository::class)->findOneByEmail($email);
        self::assertNotNull($persisted);
        self::assertNotNull(
            $this->manager->getRepository(AdherentSignupSource::class)->findOneBy([
                'adherent' => $persisted,
                'source' => 'newsletter',
            ])
        );
    }
}

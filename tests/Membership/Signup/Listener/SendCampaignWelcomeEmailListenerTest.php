<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup\Listener;

use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Mailer\Message\Campaign\CampaignWelcomeMessage;
use App\Membership\Event\UserEvent;
use App\Membership\Signup\Listener\SendCampaignWelcomeEmailListener;
use App\Membership\UserEvents;
use App\Security\Http\LoginLink\LoginLinkHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Uid\Uuid;

class SendCampaignWelcomeEmailListenerTest extends TestCase
{
    private const MAGIC_LINK = 'https://app.parti-renaissance.fr/magic-link?token=abc';

    public function testSubscribesToUserValidated(): void
    {
        self::assertArrayHasKey(UserEvents::USER_VALIDATED, SendCampaignWelcomeEmailListener::getSubscribedEvents());
    }

    public function testSendsWelcomeMailWithMagicLinkForSignupAccount(): void
    {
        $loginLinkHandler = $this->createStub(LoginLinkHandler::class);
        $loginLinkHandler
            ->method('createLoginLink')
            ->willReturn(new LoginLinkDetails(self::MAGIC_LINK, new \DateTimeImmutable('+1 day')))
        ;

        $mailer = $this->createMock(MailerService::class);
        $mailer
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::callback(static function (CampaignWelcomeMessage $message): bool {
                return self::MAGIC_LINK === $message->getVars()['magic_link'];
            }))
        ;

        new SendCampaignWelcomeEmailListener($mailer, $loginLinkHandler)->onUserValidated(new UserEvent($this->createAdherent()));
    }

    public function testDoesNotSendForNonSignupAccount(): void
    {
        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');

        $loginLinkHandler = $this->createMock(LoginLinkHandler::class);
        $loginLinkHandler->expects(self::never())->method('createLoginLink');

        $adherent = $this->createAdherent();
        $adherent->signupAccount = false;

        new SendCampaignWelcomeEmailListener($mailer, $loginLinkHandler)->onUserValidated(new UserEvent($adherent));
    }

    public function testDoesNotSendForDisabledAccount(): void
    {
        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');

        $loginLinkHandler = $this->createMock(LoginLinkHandler::class);
        $loginLinkHandler->expects(self::never())->method('createLoginLink');

        new SendCampaignWelcomeEmailListener($mailer, $loginLinkHandler)->onUserValidated(new UserEvent($this->createAdherent(Adherent::DISABLED)));
    }

    private function createAdherent(string $status = Adherent::PENDING): Adherent
    {
        $adherent = Adherent::create(
            Uuid::v4(),
            'public-id',
            'welcome@example.test',
            null,
            'male',
            'Jane',
            'Doe',
            status: $status,
        );
        $adherent->signupAccount = true;

        return $adherent;
    }
}

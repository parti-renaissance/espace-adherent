<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup\Handler;

use App\Adhesion\ActivationCodeManager;
use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Entity\PostAddress;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\SignupConfirmationMessage;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use App\Membership\Signup\Handler\SendSignupConfirmationCommandHandler;
use App\Security\Http\LoginLink\LoginLinkHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

class SendSignupConfirmationCommandHandlerTest extends TestCase
{
    public function testInvokeGeneratesCodeAndMagicLinkAndSendsConfirmationMail(): void
    {
        $adherent = $this->createAdherent('jane.doe@example.org', 'renaissance');
        $magicLinkUrl = 'https://app.example.org/connexion/lien-magique?user=...&hash=...';
        $token = AdherentActivationCode::create($adherent, 10, 3);

        $loginLinkHandler = $this->createMock(LoginLinkHandler::class);
        $loginLinkHandler
            ->expects(self::once())
            ->method('createLoginLink')
            ->with($adherent, null, 86400, 'renaissance')
            ->willReturn(new LoginLinkDetails($magicLinkUrl, new \DateTimeImmutable('+1 day')))
        ;

        $activationCodeManager = $this->createMock(ActivationCodeManager::class);
        $activationCodeManager
            ->expects(self::once())
            ->method('generate')
            ->with(
                $adherent,
                true,
                SendSignupConfirmationCommandHandler::SIGNUP_CODE_LENGTH,
            )
            ->willReturn($token)
        ;

        $mailerService = $this->createMock(MailerService::class);
        $mailerService
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::callback(static function (SignupConfirmationMessage $message) use ($adherent, $magicLinkUrl, $token): bool {
                return $message->getVars()['magic_link'] === $magicLinkUrl
                    && $message->getVars()['code'] === $token->value
                    && $message->getVars()['first_name'] === $adherent->getFirstName();
            }))
            ->willReturn(true)
        ;

        $handler = new SendSignupConfirmationCommandHandler($mailerService, $loginLinkHandler, $activationCodeManager, new NullLogger());
        $handler(new SendSignupConfirmationCommand($adherent));
    }

    public function testInvokeLogsErrorWhenMailDeliveryFails(): void
    {
        // MailerService swallows MailerException and returns false. The command is dispatched on a
        // synchronous transport: throwing would bubble up as a 5xx on /signup. Instead, we log so
        // the failure is observable without breaking the user-facing flow. The user can retry via
        // /signup/resend-code (subject to exponential backoff).
        $adherent = $this->createAdherent('mail-failure@example.org', 'renaissance');
        $token = AdherentActivationCode::create($adherent, 10, 3);

        $loginLinkHandler = $this->createMock(LoginLinkHandler::class);
        $loginLinkHandler
            ->expects(self::once())
            ->method('createLoginLink')
            ->with($adherent, null, 86400, 'renaissance')
            ->willReturn(new LoginLinkDetails('https://app.example.org/magic', new \DateTimeImmutable('+1 day')))
        ;

        $activationCodeManager = $this->createMock(ActivationCodeManager::class);
        $activationCodeManager
            ->expects(self::once())
            ->method('generate')
            ->with(
                $adherent,
                true,
                SendSignupConfirmationCommandHandler::SIGNUP_CODE_LENGTH,
            )
            ->willReturn($token)
        ;

        $mailerService = $this->createMock(MailerService::class);
        $mailerService
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::isInstanceOf(SignupConfirmationMessage::class))
            ->willReturn(false)
        ;

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with(
                'Signup confirmation mail delivery failed.',
                self::callback(static fn (array $context) => isset($context['adherent_uuid']))
            )
        ;

        $handler = new SendSignupConfirmationCommandHandler($mailerService, $loginLinkHandler, $activationCodeManager, $logger);
        // No exception expected: failures are logged, not thrown.
        $handler(new SendSignupConfirmationCommand($adherent));
    }

    private function createAdherent(string $email, ?string $source): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($email),
            'ABC-100',
            $email,
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );

        if (null !== $source) {
            $adherent->setSource($source);
        }

        return $adherent;
    }
}

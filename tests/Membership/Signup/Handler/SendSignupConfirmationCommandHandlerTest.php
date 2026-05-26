<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup\Handler;

use App\Entity\Adherent;
use App\Entity\PostAddress;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\SignupConfirmationMessage;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Signup\Command\SendSignupConfirmationCommand;
use App\Membership\Signup\Handler\SendSignupConfirmationCommandHandler;
use App\Security\Http\LoginLink\LoginLinkHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

class SendSignupConfirmationCommandHandlerTest extends TestCase
{
    public function testInvokeGeneratesMagicLink24hAndSendsConfirmationMail(): void
    {
        $adherent = $this->createAdherent('jane.doe@example.org', 'renaissance');
        $magicLinkUrl = 'https://app.example.org/connexion/lien-magique?user=...&hash=...';

        $loginLinkHandler = $this->createMock(LoginLinkHandler::class);
        $loginLinkHandler
            ->expects(self::once())
            ->method('createLoginLink')
            ->with($adherent, null, 86400, 'renaissance')
            ->willReturn(new LoginLinkDetails($magicLinkUrl, new \DateTimeImmutable('+1 day')))
        ;

        $mailerService = $this->createMock(MailerService::class);
        $mailerService
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::callback(static function (SignupConfirmationMessage $message) use ($adherent, $magicLinkUrl): bool {
                return $message->getVars()['magic_link'] === $magicLinkUrl
                    && $message->getVars()['first_name'] === $adherent->getFirstName();
            }))
            ->willReturn(true)
        ;

        $handler = new SendSignupConfirmationCommandHandler($mailerService, $loginLinkHandler);
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

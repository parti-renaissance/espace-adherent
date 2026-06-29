<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Handler;

use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Adherent\Handler\SendResubscribeEmailCommandHandler;
use App\Entity\Adherent;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\AdherentResubscribeEmailMessage;
use App\Membership\ActivityPositionsEnum;
use App\Security\Http\LoginLink\LoginLinkHandler;
use libphonenumber\PhoneNumber;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Tests\App\AbstractKernelTestCase;

class SendResubscribeEmailCommandHandlerTest extends AbstractKernelTestCase
{
    public function testDoesNotSendToHardBouncedAddress(): void
    {
        $adherent = $this->makeAdherent();
        $adherent->markAsEmailHardBounced();

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');

        $this->handler($mailer)(new SendResubscribeEmailCommand($adherent));
    }

    public function testDoesNotSendToComplainedAddress(): void
    {
        $adherent = $this->makeAdherent();
        $adherent->markAsEmailComplained();

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects(self::never())->method('sendMessage');

        $this->handler($mailer)(new SendResubscribeEmailCommand($adherent));
    }

    public function testSendsToDeliverableAddress(): void
    {
        $adherent = $this->makeAdherent();

        $mailer = $this->createMock(MailerService::class);
        $mailer
            ->expects(self::once())
            ->method('sendMessage')
            ->with(self::isInstanceOf(AdherentResubscribeEmailMessage::class));

        $this->handler($mailer)(new SendResubscribeEmailCommand($adherent));
    }

    private function handler(MailerService $mailer): SendResubscribeEmailCommandHandler
    {
        // Stub the concrete handler (not the interface): the production call passes a `targetPath`
        // named argument that only exists on the concrete LoginLinkHandler signature.
        $loginLink = $this->createStub(LoginLinkHandler::class);
        $loginLink
            ->method('createLoginLink')
            ->willReturn(new LoginLinkDetails('https://login.link/resubscribe', new \DateTimeImmutable('+1 hour')));

        return new SendResubscribeEmailCommandHandler(
            $mailer,
            $loginLink,
            $this->createStub(LoggerInterface::class),
        );
    }

    private function makeAdherent(): Adherent
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $email = 'resubscribe-handler@test.dev';

        return Adherent::create(
            Adherent::createUuid($email),
            'RS-1',
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
    }
}

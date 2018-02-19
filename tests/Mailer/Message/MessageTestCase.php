<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

abstract class MessageTestCase extends TestCase
{
    protected static function assertMessage(string $class, array $vars, Message $message): void
    {
        self::assertInstanceOf($class, $message);
        self::assertInstanceOf(Message::class, $message);

        self::assertSame($vars, $message->getVars());
    }

    protected static function assertMessageRecipient(string $email, ?string $name, array $vars, Message $message): void
    {
        $recipient = $message->getRecipient($email);

        self::assertInstanceOf(MessageRecipient::class, $recipient);

        self::assertSame($email, $recipient->getEmailAddress());
        self::assertSame($name, $recipient->getFullName());
        self::assertSame($vars, $recipient->getVars());
    }

    protected static function assertReplyTo(?string $email, Message $message): void
    {
        self::assertSame($email, $message->getReplyTo());
    }

    protected static function assertNoReplyTo(Message $message): void
    {
        self::assertReplyTo(null, $message);
    }

    protected static function assertSender(?string $name, ?string $email, Message $message): void
    {
        self::assertSame($name, $message->getSenderName());
        self::assertSame($email, $message->getSenderEmail());
    }

    protected static function assertNoSender(Message $message): void
    {
        self::assertSender(null, null, $message);
    }

    protected static function assertCountRecipients(int $count, Message $message): void
    {
        self::assertCount($count, $message->getRecipients());
    }

    protected static function assertCountCC(int $count, Message $message): void
    {
        self::assertCount($count, $message->getCC());
    }

    protected static function assertNoCC(Message $message): void
    {
        self::assertEmpty($message->getCC());
    }

    protected static function assertMessageCC(string $email, Message $message): void
    {
        self::assertContains($email, $message->getCC());
    }

    protected function createAdherent(
        string $email,
        string $firstName,
        string $lastName
    ): Adherent {
        $adherent = $this->createMock(Adherent::class);

        $adherent
            ->expects(self::any())
            ->method('getEmailAddress')
            ->willReturn($email)
        ;
        $adherent
            ->expects(self::any())
            ->method('getFullName')
            ->willReturn("$firstName $lastName")
        ;
        $adherent
            ->expects(self::any())
            ->method('getFirstName')
            ->willReturn($firstName)
        ;
        $adherent
            ->expects(self::any())
            ->method('getLastName')
            ->willReturn($lastName)
        ;
        $adherent
            ->expects(self::any())
            ->method('getLastNameInitial')
            ->willReturn(strtoupper($lastName[0].'.'))
        ;

        return $adherent;
    }

    protected function createEventRegistration(
        string $email,
        string $firstName,
        string $lastName
    ): EventRegistration {
        $registration = $this->createMock(EventRegistration::class);

        $registration
            ->expects(self::any())
            ->method('getEmailAddress')
            ->willReturn($email)
        ;
        $registration
            ->expects(self::any())
            ->method('getFullName')
            ->willReturn("$firstName $lastName")
        ;
        $registration
            ->expects(self::any())
            ->method('getFirstName')
            ->willReturn($firstName)
        ;
        $registration
            ->expects(self::any())
            ->method('getLastName')
            ->willReturn($lastName)
        ;

        return $registration;
    }

    protected static function createPhoneNumber(string $number): PhoneNumber
    {
        $phoneNumber = new PhoneNumber();

        $phoneNumber->setCountryCode('FR');
        $phoneNumber->setNationalNumber($number);

        return $phoneNumber;
    }
}

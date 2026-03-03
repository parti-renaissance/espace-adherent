<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp;

use App\Entity\Adherent;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use App\Mailchimp\Driver;
use App\Mailchimp\Exception\FailedSyncException;
use App\Mailchimp\Exception\InvalidContactEmailException;
use App\Mailchimp\Exception\InvalidPayloadException;
use App\Mailchimp\Exception\RemovedContactStatusException;
use App\Mailchimp\Exception\SmsPhoneAlreadySubscribedException;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AdherentChangeCommand;
use App\Mailchimp\Synchronisation\Request\ContactRequest;
use App\Mailchimp\Synchronisation\Request\MemberRequest;
use App\Mailchimp\Synchronisation\RequestBuilder;
use App\Repository\SmsOptOutRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ManagerTest extends TestCase
{
    private Driver&MockObject $driver;
    private MessageBusInterface&MockObject $bus;
    private MailchimpObjectIdMapping&MockObject $mailchimpObjectIdMapping;
    private ServiceLocator&MockObject $requestBuildersLocator;
    private RequestBuilder&MockObject $requestBuilder;
    private SmsOptOutRepository&MockObject $smsBlacklistChecker;
    private Manager $manager;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(Driver::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->mailchimpObjectIdMapping = $this->createMock(MailchimpObjectIdMapping::class);
        $this->requestBuildersLocator = $this->createMock(ServiceLocator::class);
        $this->requestBuilder = $this->createMock(RequestBuilder::class);
        $this->smsBlacklistChecker = $this->createMock(SmsOptOutRepository::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->requestBuildersLocator
            ->method('get')
            ->with(RequestBuilder::class)
            ->willReturn($this->requestBuilder)
        ;

        $this->manager = new Manager(
            $this->driver,
            $eventDispatcher,
            $this->mailchimpObjectIdMapping,
            $this->bus,
            $this->requestBuildersLocator,
            $this->smsBlacklistChecker,
        );
    }

    public function testEditMemberSuccessNominal(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->driver
            ->expects($this->once())
            ->method('addContact')
            ->with($contactRequest, 'list-id', true)
            ->willReturn('new-contact-id')
        ;

        // Fallback should NOT be called
        $this->driver
            ->expects($this->never())
            ->method('editMember')
        ;

        // Redispatch should NOT be called
        $this->bus
            ->expects($this->never())
            ->method('dispatch')
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('new-contact-id', $adherent->mailchimpContactId);
        self::assertNull($adherent->emailStatusComment);
    }

    public function testEditMemberFallbackOnInvalidPayloadException(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');
        $memberRequest = new MemberRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->requestBuilder
            ->method('buildMemberRequest')
            ->with('test@example.com')
            ->willReturn($memberRequest)
        ;

        // First call fails with InvalidPayloadException
        $this->driver
            ->expects($this->once())
            ->method('addContact')
            ->willThrowException(new InvalidPayloadException('Invalid Resource'))
        ;

        // Fallback to editMember should be called with throw=true
        $this->driver
            ->expects($this->once())
            ->method('editMember')
            ->with($memberRequest, 'list-id', true)
            ->willReturn(true)
        ;

        $this->manager->editMember($adherent, $message);

        self::assertNull($adherent->emailStatusComment);
    }

    public function testEditMemberRedispatchOnRemovedContactStatusException(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        // Call fails with RemovedContactStatusException
        $this->driver
            ->expects($this->once())
            ->method('addContact')
            ->willThrowException(new RemovedContactStatusException('Unsubscribed'))
        ;

        // Should dispatch the original message with DelayStamp(5000)
        $this->bus
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                self::identicalTo($message),
                self::callback(function (array $stamps): bool {
                    if (1 !== \count($stamps)) {
                        return false;
                    }
                    $stamp = $stamps[0];
                    if (!$stamp instanceof DelayStamp) {
                        return false;
                    }

                    return 5000 === $stamp->getDelay();
                })
            )
            ->willReturn(new Envelope($message))
        ;

        $this->manager->editMember($adherent, $message);

        self::assertFalse($adherent->isEmailSubscribed());
        self::assertSame('Unsubscribed', $adherent->emailStatusComment);
    }

    public function testEditMemberHandlesInvalidContactEmailException(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->driver
            ->expects($this->once())
            ->method('addContact')
            ->willThrowException(new InvalidContactEmailException())
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('Email invalid', $adherent->emailStatusComment);
    }

    public function testEditMemberHandlesFailedSyncException(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->driver
            ->expects($this->once())
            ->method('addContact')
            ->willThrowException(new FailedSyncException('Internal Server Error'))
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('Internal Server Error', $adherent->lastMailchimpFailedSyncResponse);
    }

    public function testEditMemberUpdateContactFallbackOnInvalidPayloadException(): void
    {
        $adherent = $this->createAdherent();
        $adherent->mailchimpContactId = 'existing-contact-id';
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');
        $memberRequest = new MemberRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->requestBuilder
            ->method('buildMemberRequest')
            ->with('test@example.com')
            ->willReturn($memberRequest)
        ;

        // updateContact fails with InvalidPayloadException
        $this->driver
            ->expects($this->once())
            ->method('updateContact')
            ->with('existing-contact-id', $contactRequest, 'list-id', true)
            ->willThrowException(new InvalidPayloadException('Invalid phone'))
        ;

        // Fallback to editMember should be called with throw=true
        $this->driver
            ->expects($this->once())
            ->method('editMember')
            ->with($memberRequest, 'list-id', true)
            ->willReturn(true)
        ;

        $this->manager->editMember($adherent, $message);

        self::assertNull($adherent->emailStatusComment);
    }

    public function testEditMemberOnSmsPhoneAlreadySubscribedBlacklistsAndRetries(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');
        $contactRequest->setSmsPhone('+33612345678');
        $contactRequestWithoutSms = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        // First call returns request with SMS, second call returns request without SMS (after blacklist)
        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturnOnConsecutiveCalls($contactRequest, $contactRequestWithoutSms)
        ;

        // First call fails with SmsPhoneAlreadySubscribedException
        $this->driver
            ->expects($this->exactly(2))
            ->method('addContact')
            ->willReturnCallback(function () {
                static $callCount = 0;
                ++$callCount;

                if (1 === $callCount) {
                    throw new SmsPhoneAlreadySubscribedException('+33612345678', 'already subscribed to another contact');
                }

                return 'new-contact-id';
            })
        ;

        // Should blacklist the phone (flush happens inside add())
        $this->smsBlacklistChecker
            ->expects($this->once())
            ->method('add')
            ->with('+33612345678', SmsOptOutSourceEnum::Mailchimp)
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('new-contact-id', $adherent->mailchimpContactId);
        self::assertNull($adherent->emailStatusComment);
    }

    public function testEditMemberOnSmsPhoneAlreadySubscribedWithExistingContactUpdates(): void
    {
        $adherent = $this->createAdherent();
        $adherent->mailchimpContactId = 'existing-contact-id';
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');
        $contactRequest->setSmsPhone('+33698765432');
        $contactRequestWithoutSms = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturnOnConsecutiveCalls($contactRequest, $contactRequestWithoutSms)
        ;

        // First updateContact call fails, second succeeds
        $this->driver
            ->expects($this->exactly(2))
            ->method('updateContact')
            ->willReturnCallback(function (string $contactId, ContactRequest $request, string $listId, bool $throw) {
                static $callCount = 0;
                ++$callCount;

                self::assertTrue($throw, 'updateContact should be called with throw=true');

                if (1 === $callCount) {
                    throw new SmsPhoneAlreadySubscribedException('+33698765432', 'already subscribed to another contact');
                }

                return true;
            })
        ;

        // Should blacklist the phone (flush happens inside add())
        $this->smsBlacklistChecker
            ->expects($this->once())
            ->method('add')
            ->with('+33698765432', SmsOptOutSourceEnum::Mailchimp)
        ;

        $this->manager->editMember($adherent, $message);

        self::assertNull($adherent->emailStatusComment);
    }

    public function testEditMemberContactIdRetrievedFromMemberInfo(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        self::assertNull($adherent->mailchimpContactId);

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        // getMemberInfo returns contact_id alongside status
        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => 'fetched-contact-id'])
        ;

        $this->requestBuilder
            ->method('updateFromAdherent')
            ->willReturnSelf()
        ;

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        // Since contact_id was found, updateContact should be called (not addContact)
        $this->driver
            ->expects($this->once())
            ->method('updateContact')
            ->with('fetched-contact-id', $contactRequest, 'list-id', true)
            ->willReturn(true)
        ;

        $this->driver
            ->expects($this->never())
            ->method('addContact')
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('fetched-contact-id', $adherent->mailchimpContactId);
    }

    private function createAdherent(): Adherent
    {
        $adherent = new Adherent();
        $adherent->setEmailAddress('test@example.com');
        // Set UUID via reflection (required for getUuid())
        $reflection = new \ReflectionClass($adherent);
        $property = $reflection->getProperty('uuid');
        $property->setValue($adherent, Uuid::uuid4());

        return $adherent;
    }

    private function createCommand(Adherent $adherent): AdherentChangeCommand
    {
        return new AdherentChangeCommand(Uuid::uuid4(), $adherent->getEmailAddress());
    }
}

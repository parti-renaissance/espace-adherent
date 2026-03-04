<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp;

use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
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
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionTypeEnum;
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
    private SmsOptOutRepository&MockObject $smsOptOutRepository;
    private SubscriptionTypeRepository&MockObject $subscriptionTypeRepository;
    private Manager $manager;

    protected function setUp(): void
    {
        $this->driver = $this->createMock(Driver::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->mailchimpObjectIdMapping = $this->createMock(MailchimpObjectIdMapping::class);
        $this->requestBuildersLocator = $this->createMock(ServiceLocator::class);
        $this->requestBuilder = $this->createMock(RequestBuilder::class);
        $this->smsOptOutRepository = $this->createMock(SmsOptOutRepository::class);
        $this->subscriptionTypeRepository = $this->createMock(SubscriptionTypeRepository::class);

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
            $this->smsOptOutRepository,
            $this->subscriptionTypeRepository,
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

    public function testEditMemberOnSmsPhoneConflictRetriesWithoutSms(): void
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

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->requestBuilder
            ->expects($this->once())
            ->method('buildContactRequestWithoutSms')
            ->with('test@example.com')
            ->willReturn($contactRequestWithoutSms)
        ;

        // First addContact fails with SMS conflict
        $this->driver
            ->expects($this->exactly(2))
            ->method('addContact')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new SmsPhoneAlreadySubscribedException('+33612345678', 'already subscribed')),
                'new-contact-id'
            )
        ;

        // Should NOT fallback to legacy
        $this->driver->expects($this->never())->method('editMember');

        // Should NOT add to opt-out
        $this->smsOptOutRepository->expects($this->never())->method('add');

        $this->manager->editMember($adherent, $message);

        self::assertSame('new-contact-id', $adherent->mailchimpContactId);
        self::assertSame('contact', $adherent->mailchimpSyncEndpoint);
        self::assertNotNull($adherent->mailchimpLastSyncedAt);
        // Original SMS conflict error is preserved for monitoring
        self::assertSame('already subscribed', $adherent->lastMailchimpFailedSyncResponse);
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
            ->willReturn(['status' => null, 'contact_id' => 'fetched-contact-id', 'sms_subscription_status' => null])
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

    public function testEditMemberOnSmsPhoneConflictRetryFails(): void
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

        $this->requestBuilder
            ->method('buildContactRequest')
            ->willReturn($contactRequest)
        ;

        $this->requestBuilder
            ->method('buildContactRequestWithoutSms')
            ->willReturn($contactRequestWithoutSms)
        ;

        // First addContact fails with SMS conflict, retry also fails
        $this->driver
            ->expects($this->exactly(2))
            ->method('addContact')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new SmsPhoneAlreadySubscribedException('+33612345678', 'already subscribed')),
                $this->throwException(new FailedSyncException('Server error'))
            )
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('Server error', $adherent->lastMailchimpFailedSyncResponse);
        self::assertNull($adherent->mailchimpSyncEndpoint);
    }

    public function testEditMemberSmsReconciliationAddsLocalSubscription(): void
    {
        $adherent = $this->createAdherent();
        $adherent->mailchimpContactId = 'existing-contact-id';
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        $smsType = new SubscriptionType('SMS', SubscriptionTypeEnum::MILITANT_ACTION_SMS);

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        // getMemberInfo returns sms_subscription_status = subscribed
        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null, 'sms_subscription_status' => 'subscribed'])
        ;

        $this->subscriptionTypeRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with(SubscriptionTypeEnum::MILITANT_ACTION_SMS)
            ->willReturn($smsType)
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
            ->method('updateContact')
            ->willReturn(true)
        ;

        self::assertFalse($adherent->hasSmsSubscriptionType());

        $this->manager->editMember($adherent, $message);

        self::assertTrue($adherent->hasSmsSubscriptionType());
    }

    public function testEditMemberSmsReconciliationRemovesLocalSubscriptionOnOptOut(): void
    {
        $adherent = $this->createAdherent();
        $adherent->mailchimpContactId = 'existing-contact-id';
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        // Give the adherent SMS subscription to verify removal
        $smsType = new SubscriptionType('SMS', SubscriptionTypeEnum::MILITANT_ACTION_SMS);
        $adherent->addSubscriptionType($smsType);
        self::assertTrue($adherent->hasSmsSubscriptionType());

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        // getMemberInfo returns sms_subscription_status = unsubscribed
        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null, 'sms_subscription_status' => 'unsubscribed'])
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
            ->method('updateContact')
            ->willReturn(true)
        ;

        $this->manager->editMember($adherent, $message);

        self::assertFalse($adherent->hasSmsSubscriptionType());
    }

    public function testEditMemberNoSmsStatusSkipsReconciliation(): void
    {
        $adherent = $this->createAdherent();
        $message = $this->createCommand($adherent);
        $contactRequest = new ContactRequest('test@example.com');

        $this->mailchimpObjectIdMapping
            ->method('getListIdFromSource')
            ->willReturn('list-id')
        ;

        // No sms_subscription_status returned
        $this->driver
            ->method('getMemberInfo')
            ->willReturn(['status' => null, 'contact_id' => null, 'sms_subscription_status' => null])
        ;

        // Should NOT try to reconcile SMS
        $this->subscriptionTypeRepository
            ->expects($this->never())
            ->method('findOneByCode')
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
            ->method('addContact')
            ->willReturn('new-contact-id')
        ;

        $this->manager->editMember($adherent, $message);
    }

    public function testEditMemberMonitoringColumnsOnSuccess(): void
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
            ->method('addContact')
            ->willReturn('new-contact-id')
        ;

        self::assertNull($adherent->mailchimpSyncEndpoint);
        self::assertNull($adherent->mailchimpLastSyncedAt);

        $this->manager->editMember($adherent, $message);

        self::assertSame('contact', $adherent->mailchimpSyncEndpoint);
        self::assertNotNull($adherent->mailchimpLastSyncedAt);
    }

    public function testEditMemberMonitoringColumnsOnLegacyFallback(): void
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
            ->willReturn($memberRequest)
        ;

        $this->driver
            ->method('addContact')
            ->willThrowException(new InvalidPayloadException('Invalid Resource'))
        ;

        $this->driver
            ->method('editMember')
            ->willReturn(true)
        ;

        $this->manager->editMember($adherent, $message);

        self::assertSame('member', $adherent->mailchimpSyncEndpoint);
        self::assertNotNull($adherent->mailchimpLastSyncedAt);
        // BETA error is preserved for monitoring
        self::assertSame('Invalid Resource', $adherent->lastMailchimpFailedSyncResponse);
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

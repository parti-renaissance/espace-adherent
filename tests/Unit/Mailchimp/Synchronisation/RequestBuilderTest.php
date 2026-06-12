<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Synchronisation;

use App\Adherent\Tag\TagTranslator;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Contact\MarketingConsentStatusEnum;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use App\Mailchimp\Synchronisation\RequestBuilder;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use App\Repository\DonationRepository;
use App\Repository\Geo\ZoneRepository;
use App\Repository\SmsOptOutRepository;
use App\Utils\PhoneNumberUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RequestBuilderTest extends TestCase
{
    private RequestBuilder $requestBuilder;
    private SmsOptOutRepository&MockObject $smsOptOutRepository;
    private ZoneRepository&MockObject $zoneRepository;

    protected function setUp(): void
    {
        $this->smsOptOutRepository = $this->createMock(SmsOptOutRepository::class);
        $this->zoneRepository = $this->createMock(ZoneRepository::class);

        $this->requestBuilder = new RequestBuilder(
            $this->createStub(MailchimpObjectIdMapping::class),
            $this->createStub(ElectedRepresentativeTagsBuilder::class),
            $this->createStub(ElectedRepresentativeAdherentMandateRepository::class),
            $this->createStub(DonationRepository::class),
            $this->createStub(TagTranslator::class),
            $this->zoneRepository,
            $this->createStub(TranslatorInterface::class),
            $this->smsOptOutRepository,
        );
    }

    public function testBuildContactRequestWithOptedOutPhoneDoesNotIncludeSmsChannel(): void
    {
        $phone = PhoneNumberUtils::create('+33612345678');
        $formattedPhone = '+33612345678';
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::once())
            ->method('isOptedOut')
            ->with($formattedPhone)
            ->willReturn(true)
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder
            ->setPhone($phone)
            ->setSmsSubscribed(true)
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
        self::assertArrayHasKey('email_channel', $data);
    }

    public function testBuildContactRequestWithNonOptedOutPhoneIncludesSmsChannel(): void
    {
        $phone = PhoneNumberUtils::create('+33612345678');
        $formattedPhone = '+33612345678';
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::once())
            ->method('isOptedOut')
            ->with($formattedPhone)
            ->willReturn(false)
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder
            ->setPhone($phone)
            ->setSmsSubscribed(true)
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayHasKey('sms_channel', $data);
        self::assertSame($formattedPhone, $data['sms_channel']['sms_phone']);
        self::assertSame('confirmed', $data['sms_channel']['marketing_consent']['status']);
    }

    public function testBuildContactRequestWithNonFrenchPhoneDoesNotIncludeSmsChannel(): void
    {
        $phone = PhoneNumberUtils::create('+15551234567');
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder
            ->setPhone($phone)
            ->setSmsSubscribed(true)
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
        self::assertArrayHasKey('email_channel', $data);
    }

    public function testBuildContactRequestWithFrenchLandlineDoesNotIncludeSmsChannel(): void
    {
        $phone = PhoneNumberUtils::create('+33143444259'); // Paris landline
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder
            ->setPhone($phone)
            ->setSmsSubscribed(true)
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
        self::assertArrayHasKey('email_channel', $data);
    }

    public function testBuildContactRequestWithoutPhoneDoesNotCheckOptOut(): void
    {
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
    }

    public function testBuildContactRequestWithNullPhoneDoesNotCheckOptOut(): void
    {
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder
            ->setPhone(null)
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
    }

    public function testBuildContactRequestAlwaysIncludesEmailChannel(): void
    {
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;

        $this->requestBuilder->setEmailMarketingConsent(MarketingConsentStatusEnum::DENIED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayHasKey('email_channel', $data);
        self::assertSame($email, $data['email_channel']['email']);
        self::assertSame('denied', $data['email_channel']['marketing_consent']['status']);
    }

    public function testUpdateFromNationalEventInscriptionWithoutPostalCodeDoesNotQueryZones(): void
    {
        $this->zoneRepository
            ->expects(self::never())
            ->method('findByPostalCode')
        ;
        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;

        $result = $this->requestBuilder->updateFromNationalEventInscription($this->createEventInscription(null));

        self::assertSame($this->requestBuilder, $result);
    }

    public function testUpdateFromNationalEventInscriptionWithPostalCodeQueriesZones(): void
    {
        $this->zoneRepository
            ->expects(self::once())
            ->method('findByPostalCode')
            ->with('75001')
            ->willReturn([])
        ;
        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isOptedOut')
        ;

        $this->requestBuilder->updateFromNationalEventInscription($this->createEventInscription('75001'));
    }

    private function createEventInscription(?string $postalCode): EventInscription
    {
        $event = new NationalEvent();
        $event->updateSlug('national-event');

        $inscription = new EventInscription($event);
        $inscription->setCreatedAt(new \DateTime());
        $inscription->addressEmail = 'inscription@example.com';
        $inscription->postalCode = $postalCode;

        return $inscription;
    }
}

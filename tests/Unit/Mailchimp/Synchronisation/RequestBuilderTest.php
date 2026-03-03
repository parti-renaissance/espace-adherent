<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Synchronisation;

use App\Adherent\Tag\TagTranslator;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Contact\MarketingConsentStatusEnum;
use App\Mailchimp\Synchronisation\ElectedRepresentativeTagsBuilder;
use App\Mailchimp\Synchronisation\RequestBuilder;
use App\Repository\AdherentMandate\ElectedRepresentativeAdherentMandateRepository;
use App\Repository\DonationRepository;
use App\Repository\Geo\ZoneRepository;
use App\Repository\SmsOptOutRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RequestBuilderTest extends TestCase
{
    private RequestBuilder $requestBuilder;
    private SmsOptOutRepository&MockObject $smsOptOutRepository;

    protected function setUp(): void
    {
        $this->smsOptOutRepository = $this->createMock(SmsOptOutRepository::class);

        $this->requestBuilder = new RequestBuilder(
            $this->createMock(MailchimpObjectIdMapping::class),
            $this->createMock(ElectedRepresentativeTagsBuilder::class),
            $this->createMock(ElectedRepresentativeAdherentMandateRepository::class),
            $this->createMock(DonationRepository::class),
            $this->createMock(TagTranslator::class),
            $this->createMock(ZoneRepository::class),
            $this->createMock(TranslatorInterface::class),
            $this->smsOptOutRepository,
        );
    }

    public function testBuildContactRequestWithBlacklistedPhoneDoesNotIncludeSmsChannel(): void
    {
        $phone = '+33612345678';
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::once())
            ->method('isBlacklisted')
            ->with($phone)
            ->willReturn(true)
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

    public function testBuildContactRequestWithNonBlacklistedPhoneIncludesSmsChannel(): void
    {
        $phone = '+33612345678';
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::once())
            ->method('isBlacklisted')
            ->with($phone)
            ->willReturn(false)
        ;

        $this->requestBuilder
            ->setPhone($phone)
            ->setSmsSubscribed(true)
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayHasKey('sms_channel', $data);
        self::assertSame($phone, $data['sms_channel']['sms_phone']);
        self::assertSame('confirmed', $data['sms_channel']['marketing_consent']['status']);
    }

    public function testBuildContactRequestWithoutPhoneDoesNotCheckBlacklist(): void
    {
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isBlacklisted')
        ;

        $this->requestBuilder->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
    }

    public function testBuildContactRequestWithEmptyPhoneDoesNotCheckBlacklist(): void
    {
        $email = 'test@example.com';

        $this->smsOptOutRepository
            ->expects(self::never())
            ->method('isBlacklisted')
        ;

        $this->requestBuilder
            ->setPhone('')
            ->setEmailMarketingConsent(MarketingConsentStatusEnum::CONFIRMED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
    }

    public function testBuildContactRequestAlwaysIncludesEmailChannel(): void
    {
        $email = 'test@example.com';

        $this->requestBuilder->setEmailMarketingConsent(MarketingConsentStatusEnum::DENIED);

        $contactRequest = $this->requestBuilder->buildContactRequest($email);
        $data = $contactRequest->toArray();

        self::assertArrayHasKey('email_channel', $data);
        self::assertSame($email, $data['email_channel']['email']);
        self::assertSame('denied', $data['email_channel']['marketing_consent']['status']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Request;

use App\Mailchimp\Contact\MarketingConsentStatusEnum;
use App\Mailchimp\Synchronisation\Request\ContactRequest;
use PHPUnit\Framework\TestCase;

final class ContactRequestTest extends TestCase
{
    public function testToArrayWithNullPhoneDoesNotIncludeSmsChannel(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone(null);

        $data = $request->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
        self::assertArrayHasKey('email_channel', $data);
        self::assertSame('test@example.com', $data['email_channel']['email']);
    }

    public function testToArrayWithValidPhoneIncludesSmsChannel(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33612345678');
        $request->setSmsSubscribed(true);

        $data = $request->toArray();

        self::assertArrayHasKey('sms_channel', $data);
        self::assertSame('+33612345678', $data['sms_channel']['sms_phone']);
        self::assertSame('confirmed', $data['sms_channel']['marketing_consent']['status']);
    }

    public function testToArrayWithPhoneAndNotSubscribedHasUnknownSmsConsent(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33612345678');
        $request->setSmsSubscribed(false);

        $data = $request->toArray();

        self::assertArrayHasKey('sms_channel', $data);
        self::assertSame('unknown', $data['sms_channel']['marketing_consent']['status']);
    }

    public function testSetSmsPhonePreservesValueAsIs(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33 6 12 34 56 78');

        $data = $request->toArray();

        self::assertSame('+33 6 12 34 56 78', $data['sms_channel']['sms_phone']);
    }

    public function testToArrayWithoutSettingPhoneDoesNotIncludeSmsChannel(): void
    {
        $request = new ContactRequest('test@example.com');

        $data = $request->toArray();

        self::assertArrayNotHasKey('sms_channel', $data);
    }

    public function testToArrayWithEmailConsentIncludesEmailMarketingConsent(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setEmailConsent(MarketingConsentStatusEnum::CONFIRMED);

        $data = $request->toArray();

        self::assertSame('confirmed', $data['email_channel']['marketing_consent']['status']);
    }

    public function testToArrayWithMergeFieldsIncludesMergeFields(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setMergeFields(['FNAME' => 'John', 'LNAME' => 'Doe']);

        $data = $request->toArray();

        self::assertArrayHasKey('merge_fields', $data);
        self::assertSame('John', $data['merge_fields']['FNAME']);
        self::assertSame('Doe', $data['merge_fields']['LNAME']);
    }

    public function testToArrayWithNullMergeFieldValueConvertsToEmptyString(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setMergeFields(['FNAME' => null]);

        $data = $request->toArray();

        self::assertSame('', $data['merge_fields']['FNAME']);
    }

    public function testToArrayAlwaysIncludesLanguageFr(): void
    {
        $request = new ContactRequest('test@example.com');

        $data = $request->toArray();

        self::assertSame('fr', $data['language']);
    }

    public function testGetPhoneWithPhoneReturnsPhone(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33612345678');

        self::assertSame('+33612345678', $request->getPhone());
    }

    public function testGetPhoneWithoutPhoneReturnsNull(): void
    {
        $request = new ContactRequest('test@example.com');

        self::assertNull($request->getPhone());
    }

    public function testGetPhoneReturnsStoredValue(): void
    {
        $request = new ContactRequest('test@example.com');
        $request->setSmsPhone('+33 6 12 34 56 78');

        self::assertSame('+33 6 12 34 56 78', $request->getPhone());
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Fallback;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Fallback\MandrillCampaignPayloadBuilder;
use PHPUnit\Framework\TestCase;

class MandrillCampaignPayloadBuilderTest extends TestCase
{
    private const string ACCOUNT_URL = 'https://parti.re/mon-compte';

    public function testBuildsMessagesSendPayloadWithMergeVarsPerRecipient(): void
    {
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::atLeastOnce())->method('getSubject')->willReturn('Le sujet');
        $message->expects(self::once())->method('getFromName')->willReturn('Jean Dupont | Renaissance');

        $builder = new MandrillCampaignPayloadBuilder('contact@parti-renaissance.fr');

        $payload = $builder->build($message, '<p>Bonjour *|FNAME|*</p>', [
            ['email' => 'a@test.dev', 'firstName' => 'Alice', 'lastName' => 'Martin', 'gender' => 'female', 'publicId' => 'PUB-1'],
            ['email' => 'b@test.dev', 'firstName' => 'Bob', 'lastName' => 'Durand', 'gender' => 'male', 'publicId' => 'PUB-2'],
        ]);

        $msg = $payload['message'];
        self::assertSame('Le sujet', $msg['subject']);
        self::assertSame('contact@parti-renaissance.fr', $msg['from_email']);
        self::assertSame('Jean Dupont | Renaissance', $msg['from_name']);
        self::assertSame('mailchimp', $msg['merge_language']);
        self::assertSame(['Reply-To' => 'contact@parti-renaissance.fr'], $msg['headers']);

        self::assertCount(2, $msg['to']);
        self::assertSame(['email' => 'a@test.dev', 'name' => 'Alice Martin', 'type' => 'to'], $msg['to'][0]);

        self::assertCount(2, $msg['merge_vars']);
        self::assertSame('a@test.dev', $msg['merge_vars'][0]['rcpt']);
        self::assertSame(
            [
                ['name' => 'FNAME', 'content' => 'Alice'],
                ['name' => 'LNAME', 'content' => 'Martin'],
                ['name' => 'GENDER', 'content' => 'female'],
                ['name' => 'PUBLIC_ID', 'content' => 'PUB-1'],
            ],
            $msg['merge_vars'][0]['vars']
        );
    }

    public function testResolvesMailchimpSystemTagsButKeepsRecipientMergeTags(): void
    {
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::atLeastOnce())->method('getSubject')->willReturn('Le sujet');
        $message->expects(self::once())->method('getFromName')->willReturn('n');

        $builder = new MandrillCampaignPayloadBuilder('contact@parti-renaissance.fr');

        $renderedHtml = '<title>*|MC:SUBJECT|*</title>'
            .'<p>Bonjour *|FNAME|* *|IF:GENDER|*Madame*|END:IF|*</p>'
            .'<a href="*|UNSUB|*">se désabonner</a>'
            .'<a href="*|UPDATE_PROFILE|*">préférences</a>'
            .'<a href="*|ARCHIVE|*">navigateur</a>';

        $html = $builder->build($message, $renderedHtml, [
            ['email' => 'a@test.dev', 'firstName' => 'Alice', 'lastName' => 'Martin', 'gender' => 'female', 'publicId' => 'PUB-1'],
        ])['message']['html'];

        // System tags Mandrill cannot expand are resolved...
        self::assertStringContainsString('<title>Le sujet</title>', $html);
        self::assertStringContainsString('<a href="'.self::ACCOUNT_URL.'">se désabonner</a>', $html);
        self::assertStringContainsString('<a href="'.self::ACCOUNT_URL.'">préférences</a>', $html);
        self::assertStringContainsString('<a href="'.self::ACCOUNT_URL.'">navigateur</a>', $html);
        self::assertStringNotContainsString('*|MC:SUBJECT|*', $html);
        self::assertStringNotContainsString('*|UNSUB|*', $html);
        self::assertStringNotContainsString('*|UPDATE_PROFILE|*', $html);
        self::assertStringNotContainsString('*|ARCHIVE|*', $html);

        // ...but per-recipient merge tags and conditionals are left for Mandrill to expand at send.
        self::assertStringContainsString('Bonjour *|FNAME|* *|IF:GENDER|*Madame*|END:IF|*', $html);
    }

    public function testNullGenderBecomesEmptyMergeVar(): void
    {
        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::atLeastOnce())->method('getSubject')->willReturn('s');
        $message->expects(self::once())->method('getFromName')->willReturn('n');

        $builder = new MandrillCampaignPayloadBuilder('contact@parti-renaissance.fr');

        $payload = $builder->build($message, '<p>html</p>', [
            ['email' => 'c@test.dev', 'firstName' => 'Cl', 'lastName' => 'X', 'gender' => null, 'publicId' => 'PUB-3'],
        ]);

        $genderVar = $payload['message']['merge_vars'][0]['vars'][2];
        self::assertSame(['name' => 'GENDER', 'content' => ''], $genderVar);
    }
}

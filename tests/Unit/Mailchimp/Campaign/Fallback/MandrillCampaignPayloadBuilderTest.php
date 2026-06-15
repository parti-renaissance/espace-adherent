<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Fallback;

use App\AdherentMessage\Variable\Renderer;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Mailchimp\Campaign\Fallback\MandrillCampaignPayloadBuilder;
use PHPUnit\Framework\TestCase;

class MandrillCampaignPayloadBuilderTest extends TestCase
{
    public function testBuildsMessagesSendPayloadWithMergeVarsPerRecipient(): void
    {
        $renderer = $this->createMock(Renderer::class);
        $renderer
            ->expects(self::once())
            ->method('renderMailchimp')
            ->with('<p>Bonjour *|FNAME|*</p>')
            ->willReturn('<p>Bonjour *|FNAME|*</p>')
        ;

        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::once())->method('getContent')->willReturn('<p>Bonjour *|FNAME|*</p>');
        $message->expects(self::once())->method('getSubject')->willReturn('Le sujet');
        $message->expects(self::once())->method('getFromName')->willReturn('Jean Dupont | Renaissance');

        $builder = new MandrillCampaignPayloadBuilder($renderer, 'contact@parti-renaissance.fr');

        $payload = $builder->build($message, [
            ['email' => 'a@test.dev', 'firstName' => 'Alice', 'lastName' => 'Martin', 'gender' => 'female', 'publicId' => 'PUB-1'],
            ['email' => 'b@test.dev', 'firstName' => 'Bob', 'lastName' => 'Durand', 'gender' => 'male', 'publicId' => 'PUB-2'],
        ]);

        $msg = $payload['message'];
        self::assertSame('<p>Bonjour *|FNAME|*</p>', $msg['html']);
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

    public function testNullGenderBecomesEmptyMergeVar(): void
    {
        $renderer = $this->createMock(Renderer::class);
        $renderer->expects(self::once())->method('renderMailchimp')->with('html')->willReturn('html');

        $message = $this->createMock(AdherentMessageInterface::class);
        $message->expects(self::once())->method('getContent')->willReturn('html');
        $message->expects(self::once())->method('getSubject')->willReturn('s');
        $message->expects(self::once())->method('getFromName')->willReturn('n');

        $builder = new MandrillCampaignPayloadBuilder($renderer, 'contact@parti-renaissance.fr');

        $payload = $builder->build($message, [
            ['email' => 'c@test.dev', 'firstName' => 'Cl', 'lastName' => 'X', 'gender' => null, 'publicId' => 'PUB-3'],
        ]);

        $genderVar = $payload['message']['merge_vars'][0]['vars'][2];
        self::assertSame(['name' => 'GENDER', 'content' => ''], $genderVar);
    }
}

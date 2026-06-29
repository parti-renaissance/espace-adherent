<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Rendering;

use App\AdherentMessage\Variable\Dictionary;
use App\AdherentMessage\Variable\Parser;
use App\AdherentMessage\Variable\Renderer\SesVariableRenderer;
use App\Ses\Rendering\AssembledCampaignEmail;
use App\Ses\Rendering\SesRecipient;
use App\Ses\Rendering\SesRecipientContextFactory;
use App\Ses\Rendering\SesRecipientEmailFactory;
use App\ValueObject\Genders;
use PHPUnit\Framework\TestCase;

class SesRecipientEmailFactoryTest extends TestCase
{
    public function testCreateResolvesDictionaryCodesPerRecipientAndPopulatesSesEmail(): void
    {
        $assembled = new AssembledCampaignEmail(
            \sprintf(
                '<html><body><p>%s,</p><p>%s — n° %s</p></body></html>',
                $this->code(Dictionary::SALUTATION),
                $this->code(Dictionary::FIRST_NAME),
                $this->code(Dictionary::PUBLIC_ID),
            ),
            'Lettre de campagne',
            'contact@renaissance.code',
            'Contact Renaissance',
            'auteur@renaissance.code',
        );

        $factory = $this->factory();

        $female = $factory->create($assembled, new SesRecipient('alice@b.fr', 'Alice', 'Martin', Genders::FEMALE, 'A1'));
        $male = $factory->create($assembled, new SesRecipient('bob@b.fr', 'Bob', 'Durand', Genders::MALE, 'B2'));

        // Per-recipient resolution produces distinct HTML.
        self::assertStringContainsString('Chère Alice,', $female->html);
        self::assertStringContainsString('Alice — n° A1', $female->html);
        self::assertStringContainsString('Cher Bob,', $male->html);
        self::assertStringContainsString('Bob — n° B2', $male->html);
        self::assertNotSame($female->html, $male->html);

        // No residual Dictionary placeholder.
        self::assertStringNotContainsString('{{', $female->html);
        self::assertStringNotContainsString('{{', $male->html);

        // SesEmail carries the recipient address + the message-level headers from the assembled email.
        self::assertSame('alice@b.fr', $female->to);
        self::assertSame('Lettre de campagne', $female->subject);
        self::assertSame('contact@renaissance.code', $female->fromEmail);
        self::assertSame('Contact Renaissance', $female->fromName);
        self::assertSame('auteur@renaissance.code', $female->replyTo);
    }

    public function testAbsentGenderResolvesSalutationToEmptyString(): void
    {
        $assembled = new AssembledCampaignEmail(
            \sprintf('<p>[%s]</p>', $this->code(Dictionary::SALUTATION)),
            'Sujet',
            'contact@renaissance.code',
        );

        $email = $this->factory()->create($assembled, new SesRecipient('sam@b.fr', 'Sam', 'Lee', null, 'X9'));

        self::assertStringContainsString('[]', $email->html);
        self::assertStringNotContainsString('{{', $email->html);
    }

    private function factory(): SesRecipientEmailFactory
    {
        return new SesRecipientEmailFactory(new Parser(), new SesVariableRenderer(), new SesRecipientContextFactory());
    }

    private function code(string $name): string
    {
        return \sprintf('{{%s}}', $name);
    }
}

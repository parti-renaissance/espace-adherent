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
use App\Ses\Unsubscribe\UnsubscribeUrlGenerator;
use App\ValueObject\Genders;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SesRecipientEmailFactoryTest extends TestCase
{
    private const UNSUBSCRIBE_URL = 'https://vox.test/desabonnement/TOKEN';
    private const UUID = '11111111-1111-4111-8111-111111111111';

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

        $female = $factory->create($assembled, new SesRecipient('alice@b.fr', self::UUID, 'Alice', 'Martin', Genders::FEMALE, 'A1'));
        $male = $factory->create($assembled, new SesRecipient('bob@b.fr', self::UUID, 'Bob', 'Durand', Genders::MALE, 'B2'));

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

    public function testCreateResolvesUnsubscribePlaceholderAndSetsHeaderUrl(): void
    {
        $assembled = new AssembledCampaignEmail(
            '<p><a href="{{unsubscribe_url}}">Se désabonner</a></p>',
            'Sujet',
            'contact@renaissance.code',
        );

        $email = $this->factory()->create(
            $assembled,
            new SesRecipient('z@b.fr', self::UUID, 'Zoe', 'Zed', null, 'Z0')
        );

        self::assertStringNotContainsString('{{unsubscribe_url}}', $email->html);
        self::assertStringContainsString(self::UNSUBSCRIBE_URL, $email->html);
        self::assertSame(self::UNSUBSCRIBE_URL, $email->listUnsubscribeUrl);
    }

    public function testAbsentGenderResolvesSalutationToEmptyString(): void
    {
        $assembled = new AssembledCampaignEmail(
            \sprintf('<p>[%s]</p>', $this->code(Dictionary::SALUTATION)),
            'Sujet',
            'contact@renaissance.code',
        );

        $email = $this->factory()->create($assembled, new SesRecipient('sam@b.fr', self::UUID, 'Sam', 'Lee', null, 'X9'));

        self::assertStringContainsString('[]', $email->html);
        self::assertStringNotContainsString('{{', $email->html);
    }

    public function testCreatePropagatesCampaignAndAdherentUuidToSesEmail(): void
    {
        $assembled = new AssembledCampaignEmail(
            '<p>x</p>',
            'Sujet',
            'contact@renaissance.code',
            campaignUuid: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        );

        $email = $this->factory()->create(
            $assembled,
            new SesRecipient('z@b.fr', self::UUID, 'Zoe', 'Zed', null, 'Z0')
        );

        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $email->campaignUuid);
        self::assertSame(self::UUID, $email->adherentUuid);
    }

    private function factory(): SesRecipientEmailFactory
    {
        $urlGenerator = $this->createStub(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn(self::UNSUBSCRIBE_URL);

        return new SesRecipientEmailFactory(
            new Parser(),
            new SesVariableRenderer(),
            new SesRecipientContextFactory(),
            new UnsubscribeUrlGenerator($urlGenerator, 'test-secret-0123456789abcdef-0123'),
        );
    }

    private function code(string $name): string
    {
        return \sprintf('{{%s}}', $name);
    }
}

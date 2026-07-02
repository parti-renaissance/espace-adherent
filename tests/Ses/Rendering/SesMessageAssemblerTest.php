<?php

declare(strict_types=1);

namespace Tests\App\Ses\Rendering;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailer\Template\Manager;
use App\Ses\Rendering\EmailCssInliner;
use App\Ses\Rendering\PreheaderExtractor;
use App\Ses\Rendering\SesMessageAssembler;
use App\Ses\Rendering\SesRecipient;
use App\Ses\Rendering\SesRecipientEmailFactory;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\NullLogger;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class SesMessageAssemblerTest extends AbstractKernelTestCase
{
    private ?SesMessageAssembler $assembler = null;

    public function testAssemblePublicationRendersChromeBodyAndKeepsDictionaryCodes(): void
    {
        $author = $this->createAdherent('campaign-author@test.dev');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour {{Prénom}}, voici les actualités.</p>');

        $assembled = $this->assembler->assemble($message);

        // Campaign chrome with the marketing unsubscribe footer (not the transactional one).
        self::assertStringContainsString('Si vous ne souhaitez plus recevoir nos communications', $assembled->html);
        self::assertStringContainsString('{{unsubscribe_url}}', $assembled->html);
        // Publication body injected into the content slot.
        self::assertStringContainsString('voici les actualités', $assembled->html);
        // The v10 typographic scale is inlined onto the body paragraph (16px/#424245).
        self::assertMatchesRegularExpression('/<p\b[^>]*font-size:\s*16px/i', $assembled->html);
        self::assertMatchesRegularExpression('/<p\b[^>]*color:\s*#424245/i', $assembled->html);
        // The legacy in-body "Répondre" button is not rendered.
        self::assertStringNotContainsString('mailto:', $assembled->html);
        // Message-level placeholder fully substituted.
        self::assertStringNotContainsString('{{content}}', $assembled->html);
        // Recipient-level Dictionary code preserved for the per-recipient pass (Phase 5).
        self::assertStringContainsString('{{Prénom}}', $assembled->html);
        // Header metadata: the Reply-To header still points to the author (a real header, not the button).
        self::assertSame('Lettre de campagne', $assembled->subject);
        self::assertSame('contact@staging.parti-renaissance.fr', $assembled->fromEmail);
        self::assertSame('Staging SES', $assembled->fromName);
        self::assertSame('campaign-author@test.dev', $assembled->replyTo);
    }

    public function testAssembleInlinesMarginResetAndKeepsPlaceholdersSubstitutable(): void
    {
        $author = $this->createAdherent('campaign-author-2@test.dev');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        // A heading with only margin-top:0 (the Gmail bug) plus a recipient-level Dictionary code.
        $message->setContent('<h1 style="margin-top:0">Titre</h1><p>Bonjour {{Prénom}}</p>');

        $assembled = $this->assembler->assemble($message);

        // The reset is inlined onto the heading, and {{unsubscribe_url}} survives inlining (not %7B-encoded).
        self::assertMatchesRegularExpression('/<h1\b[^>]*margin:\s*0/i', $assembled->html);
        // The v10 scale is inlined onto the content heading (h1 = 22px/#1d1d1f), not just the reset.
        self::assertMatchesRegularExpression('/<h1\b[^>]*font-size:\s*22px/i', $assembled->html);
        self::assertMatchesRegularExpression('/<h1\b[^>]*color:\s*#1d1d1f/i', $assembled->html);
        self::assertStringContainsString('{{unsubscribe_url}}', $assembled->html);
        self::assertStringNotContainsString('%7B', $assembled->html);

        // Real wiring through the per-recipient factory: placeholders must still substitute after inlining.
        $recipientEmailFactory = static::getContainer()->get(SesRecipientEmailFactory::class);
        $email = $recipientEmailFactory->create($assembled, new SesRecipient(
            'jane@test.dev',
            '11111111-1111-4111-8111-111111111111',
            'Jane',
            'Doe',
        ));

        // Final recipient HTML: heading reset kept, unsubscribe URL resolved, first name substituted.
        self::assertMatchesRegularExpression('/<h1\b[^>]*margin:\s*0/i', $email->html);
        self::assertStringNotContainsString('{{unsubscribe_url}}', $email->html);
        self::assertStringNotContainsString('%7B', $email->html);
        self::assertStringContainsString('Jane', $email->html);
        self::assertStringNotContainsString('{{Prénom}}', $email->html);

        // The hidden preheader block survives the per-recipient rendering pass.
        self::assertStringContainsString('style="display:none', $email->html);
    }

    public function testAssembleInjectsHiddenPreheaderSkippingAuthorChrome(): void
    {
        $author = $this->createAdherent('campaign-author-3@test.dev');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        // Prod-like content: an author card, then the <h1> title, then the message body.
        $message->setContent(
            '<div class="padding-responsive-top"><span>Pôle Territoires</span><p>Dimitri Gritsajuk</p><p>Chef de pôle</p></div>'
            .'<h1>Actualités de la semaine</h1>'
            .'<table><tbody><tr><td><p>Bonjour {{Prénom}}, voici les infos.</p></td></tr></tbody></table>'
        );

        $assembled = $this->assembler->assemble($message);

        // A hidden preheader block is injected right after <body> with inline styles (a CSS class
        // would be visible in Gmail, which strips <head><style>).
        self::assertMatchesRegularExpression('/<body[^>]*>\s*<div style="display:none/i', $assembled->html);
        self::assertStringContainsString('mso-hide:all', $assembled->html);

        self::assertSame(1, preg_match('/<div style="display:none[^>]*>(.*?)<\/div>/i', $assembled->html, $matches));
        $preheader = $matches[1];

        // Preview starts at the heading; the author card that precedes it is skipped.
        self::assertStringStartsWith('Actualités de la semaine', $preheader);
        self::assertStringContainsString('voici les infos', $preheader);
        self::assertStringNotContainsString('Pôle Territoires', $preheader);
        self::assertStringNotContainsString('Dimitri Gritsajuk', $preheader);

        // The Dictionary code is stripped from the preheader block, but still present in the body
        // for the per-recipient pass.
        self::assertStringNotContainsString('{{Prénom}}', $preheader);
        self::assertStringContainsString('{{Prénom}}', $assembled->html);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Instantiated directly with the real Manager (fixture-seeded DB template); the inliner is pure
        // logic, so a NullLogger is enough.
        $this->assembler = new SesMessageAssembler(
            static::getContainer()->get(Manager::class),
            new EmailCssInliner(new NullLogger()),
            new PreheaderExtractor(),
        );
    }

    protected function tearDown(): void
    {
        $this->assembler = null;

        parent::tearDown();
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Ses\Rendering;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailer\Template\Manager;
use App\Ses\Rendering\SesMessageAssembler;
use PHPUnit\Framework\Attributes\Group;
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
        self::assertStringContainsString('Se désabonner', $assembled->html);
        self::assertStringContainsString('{{unsubscribe_url}}', $assembled->html);
        // Publication body injected into the template-email-block slot.
        self::assertStringContainsString('voici les actualités', $assembled->html);
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

    protected function setUp(): void
    {
        parent::setUp();

        // The service is dormant (no live caller until C3), so it may be pruned from the container.
        // Instantiate it directly with the real Manager and the fixture-seeded DB template.
        $this->assembler = new SesMessageAssembler(static::getContainer()->get(Manager::class));
    }

    protected function tearDown(): void
    {
        $this->assembler = null;

        parent::tearDown();
    }
}

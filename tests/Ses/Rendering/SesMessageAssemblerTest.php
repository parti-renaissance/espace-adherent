<?php

declare(strict_types=1);

namespace Tests\App\Ses\Rendering;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Mailer\Template\Manager;
use App\Ses\Rendering\EmailCssInliner;
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

    public function testAssembleInlinesMarginResetAndKeepsPlaceholdersSubstitutable(): void
    {
        $author = $this->createAdherent('campaign-author-2@test.dev');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        // A heading with only margin-top:0 (the Gmail bug) plus a recipient-level Dictionary code.
        $message->setContent('<h1 style="margin-top:0">Titre</h1><p>Bonjour {{Prénom}}</p>');

        $assembled = $this->assembler->assemble($message);

        // The reset is inlined onto the heading, and {{unsubscribe_url}} survives inlining (not %7B-encoded).
        self::assertMatchesRegularExpression('/<h1[^>]*style="[^"]*margin:\s*0/i', $assembled->html);
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
        self::assertMatchesRegularExpression('/<h1[^>]*style="[^"]*margin:\s*0/i', $email->html);
        self::assertStringNotContainsString('{{unsubscribe_url}}', $email->html);
        self::assertStringNotContainsString('%7B', $email->html);
        self::assertStringContainsString('Jane', $email->html);
        self::assertStringNotContainsString('{{Prénom}}', $email->html);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Instantiated directly with the real Manager (fixture-seeded DB template); the inliner is pure
        // logic, so a NullLogger is enough.
        $this->assembler = new SesMessageAssembler(
            static::getContainer()->get(Manager::class),
            new EmailCssInliner(new NullLogger()),
        );
    }

    protected function tearDown(): void
    {
        $this->assembler = null;

        parent::tearDown();
    }
}

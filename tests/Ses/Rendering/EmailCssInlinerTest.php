<?php

declare(strict_types=1);

namespace Tests\App\Ses\Rendering;

use App\Ses\Rendering\EmailCssInliner;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\NullLogger;

class EmailCssInlinerTest extends TestCase
{
    private const HTML = <<<'HTML'
        <!DOCTYPE html><html><head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">@media (max-width:480px){.x{display:none}}p{color:#000}</style>
        </head><body>
        <!--[if mso]><table><![endif]-->
        <h1 style="margin-top:0">{{Prénom}}</h1>
        <p style="margin-top:0">Bonjour</p>
        <ul style="margin-top:0"><li>a</li></ul>
        <a href="{{unsubscribe_url}}">Se désabonner</a>
        <!--[if mso]></table><![endif]-->
        </body></html>
        HTML;

    public function testInlinesMarginResetOnHeadingsParagraphsAndLists(): void
    {
        $out = $this->inline(self::HTML);

        self::assertMatchesRegularExpression('/<h1[^>]*style="[^"]*margin:\s*0/i', $out);
        self::assertMatchesRegularExpression('/<p[^>]*style="[^"]*margin:\s*0/i', $out);
        self::assertMatchesRegularExpression('/<ul[^>]*style="[^"]*margin:\s*0/i', $out);
    }

    public function testPreservesMediaQueriesAndMsoConditionalComments(): void
    {
        $out = $this->inline(self::HTML);

        self::assertStringContainsString('@media', $out);
        self::assertStringContainsString('<!--[if mso]>', $out);
    }

    public function testRestoresPlaceholdersEncodedInUrlAttributes(): void
    {
        $out = $this->inline(self::HTML);

        // DOMDocument would leave href="%7B%7Bunsubscribe_url%7D%7D"; the inliner restores the literal braces.
        self::assertStringContainsString('href="{{unsubscribe_url}}"', $out);
        self::assertStringNotContainsString('%7B', $out);
    }

    public function testKeepsTextPlaceholdersAndAccentsIntact(): void
    {
        $out = $this->inline(self::HTML);

        self::assertStringContainsString('{{Prénom}}', $out);
        self::assertStringContainsString('Se désabonner', $out);
    }

    public function testFallsBackToInputAndLogsWhenInliningThrows(): void
    {
        $logger = new class extends AbstractLogger {
            public array $levels = [];

            public function log($level, \Stringable|string $message, array $context = []): void
            {
                $this->levels[] = $level;
            }
        };

        // Emogrifier rejects an empty document: inline() must swallow the throw and return the input verbatim.
        $result = new EmailCssInliner($logger)->inline('');

        self::assertSame('', $result);
        self::assertContains('warning', $logger->levels);
    }

    private function inline(string $html): string
    {
        return new EmailCssInliner(new NullLogger())->inline($html);
    }
}

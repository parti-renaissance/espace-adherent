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

    public function testInlinesV10TypographyScopedToContentWrapper(): void
    {
        $html = <<<'HTML'
            <!DOCTYPE html><html><head></head><body>
            <div class="content"><h1>Titre</h1><p>Corps</p><ul><li>a</li></ul><a href="#">lien</a></div>
            <div style="font-size:11px;color:#8b8d91"><p>pied de page</p></div>
            </body></html>
            HTML;

        $out = $this->inline($html);

        // Content elements pick up the v10 scale (h1 = 22px/#1d1d1f, p = 16px/#424245, link = #4291E1).
        // Emogrifier single-quotes the style attr when a value contains " (font-family), so the checks
        // stay quote-agnostic (bounded by the tag's closing >).
        self::assertMatchesRegularExpression('/<h1\b[^>]*font-size:\s*22px/i', $out);
        self::assertMatchesRegularExpression('/<h1\b[^>]*color:\s*#1d1d1f/i', $out);
        self::assertMatchesRegularExpression('/<p\b[^>]*font-size:\s*16px/i', $out);
        self::assertMatchesRegularExpression('/<p\b[^>]*color:\s*#424245/i', $out);
        self::assertMatchesRegularExpression('/<a\b[^>]*color:\s*#4291E1/i', $out);

        // The chrome footer paragraph, outside .content, only gets the global margin reset — the
        // v10 body size/color must NOT leak onto it (it would clobber the 11px/#8b8d91 footer).
        self::assertSame(1, preg_match('/<p([^>]*)>pied de page/i', $out, $footer));
        self::assertStringNotContainsString('font-size', $footer[1]);
        self::assertStringNotContainsString('#424245', $footer[1]);
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

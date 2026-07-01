<?php

declare(strict_types=1);

namespace Tests\App\Ses\Rendering;

use App\Ses\Rendering\PreheaderExtractor;
use PHPUnit\Framework\TestCase;

class PreheaderExtractorTest extends TestCase
{
    public function testStripsHtmlTagsFromContent(): void
    {
        self::assertSame(
            'Bonjour tout le monde',
            $this->extract('<p>Bonjour <strong>tout</strong> le monde</p>')
        );
    }

    public function testRemovesUnresolvedDictionaryCodes(): void
    {
        $result = $this->extract('<p>Bonjour {{Prénom}}, ravi de vous revoir</p>');

        self::assertStringNotContainsString('{{', $result);
        self::assertStringNotContainsString('Prénom', $result);
        self::assertStringContainsString('Bonjour', $result);
        self::assertStringContainsString('ravi de vous revoir', $result);
    }

    public function testSkipsAuthorChromeBeforeFirstHeading(): void
    {
        $content = '<div class="padding-responsive-top"><span>Pôle Territoires</span><p>Dimitri Gritsajuk</p><p>Chef de pôle</p></div>'
            .'<h1>Titre du message</h1>'
            .'<table><tbody><tr><td><p>Hello {{Prénom}}</p><p>est-ce que tu aimes cet email ?</p></td></tr></tbody></table>';

        $result = $this->extract($content);

        self::assertStringNotContainsString('Pôle Territoires', $result);
        self::assertStringNotContainsString('Dimitri Gritsajuk', $result);
        self::assertStringNotContainsString('Chef de pôle', $result);
        self::assertStringStartsWith('Titre du message', $result);
        self::assertStringContainsString('est-ce que tu aimes cet email', $result);
    }

    public function testUsesWholeContentWhenNoHeading(): void
    {
        // No <h1>: nothing to skip, the whole content becomes the preview.
        self::assertSame('Bonjour tout le monde', $this->extract('<p>Bonjour tout le monde</p>'));
    }

    public function testMatchesHeadingTagCaseInsensitively(): void
    {
        $result = $this->extract('<div><p>Chrome auteur</p></div><H1>Titre</H1><p>Corps du message</p>');

        self::assertStringNotContainsString('Chrome auteur', $result);
        self::assertStringStartsWith('Titre', $result);
    }

    public function testDecodesHtmlEntities(): void
    {
        self::assertSame('Café & thé', $this->extract('<p>Caf&eacute; &amp; th&eacute;</p>'));
    }

    public function testCollapsesWhitespace(): void
    {
        self::assertSame('Ligne 1 Ligne 2', $this->extract("<p>Ligne 1</p>\n\n<p>   Ligne 2</p>"));
    }

    public function testTruncatesToNinetyMultibyteCharacters(): void
    {
        $content = '<p>'.str_repeat('é', 200).'</p>';

        $result = $this->extract($content);

        self::assertSame(90, mb_strlen($result));
        self::assertSame(str_repeat('é', 90), $result);
        self::assertTrue(mb_check_encoding($result, 'UTF-8'));
    }

    public function testReturnsEmptyStringForNullContent(): void
    {
        self::assertSame('', $this->extract(null));
    }

    public function testReturnsEmptyStringForBlankContent(): void
    {
        self::assertSame('', $this->extract('   '));
        self::assertSame('', $this->extract('<p> </p>'));
    }

    public function testDoesNotEscapeOutput(): void
    {
        // After decoding, real < and & must survive verbatim: escaping is delegated to the caller.
        $result = $this->extract('<p>&lt;tag&gt; A &amp; B</p>');

        self::assertStringContainsString('<tag>', $result);
        self::assertStringContainsString('&', $result);
        self::assertStringNotContainsString('&amp;', $result);
    }

    private function extract(?string $content): string
    {
        return new PreheaderExtractor()->extract($content);
    }
}

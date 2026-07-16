<?php

declare(strict_types=1);

namespace Tests\App\Twig;

use App\Twig\BoContentExtension;
use PHPUnit\Framework\TestCase;

class BoContentExtensionTest extends TestCase
{
    private ?BoContentExtension $extension = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension = new BoContentExtension();
    }

    protected function tearDown(): void
    {
        $this->extension = null;

        parent::tearDown();
    }

    public function testRenderWrapsContentInStylingScope(): void
    {
        self::assertSame(
            '<div class="formatted-text"><p>Rendez-vous à Lyon.</p></div>',
            $this->extension->renderBoContent('<p>Rendez-vous à Lyon.</p>')
        );
    }

    public function testRenderKeepsEmptyParagraphsUsedAsLineBreaks(): void
    {
        $html = '<p>Premier.</p><p></p><p>Second.</p>';

        self::assertSame(
            '<div class="formatted-text">'.$html.'</div>',
            $this->extension->renderBoContent($html)
        );
    }

    public function testRenderNullContentReturnsNothing(): void
    {
        self::assertSame('', $this->extension->renderBoContent(null));
    }

    public function testRenderBlankContentReturnsNothing(): void
    {
        self::assertSame('', $this->extension->renderBoContent('   '));
    }

    public function testFilterIsMarkedHtmlSafeToRenderUnescaped(): void
    {
        $filters = $this->extension->getFilters();

        self::assertCount(1, $filters);
        self::assertSame('bo_html', $filters[0]->getName());
        self::assertContains('html', $filters[0]->getSafe(new \Twig\Node\Node()));
    }
}

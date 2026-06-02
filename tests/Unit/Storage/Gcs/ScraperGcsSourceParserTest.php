<?php

declare(strict_types=1);

namespace Tests\App\Unit\Storage\Gcs;

use App\Storage\Gcs\ScraperGcsSourceParser;
use PHPUnit\Framework\TestCase;

class ScraperGcsSourceParserTest extends TestCase
{
    public function testParseValidUriReturnsBucketAndObject(): void
    {
        $parser = new ScraperGcsSourceParser('scraper-a, scraper-b');

        self::assertSame(
            ['scraper-a', 'bronze/twitter/123/media/0.mp4'],
            $parser->parse('gs://scraper-a/bronze/twitter/123/media/0.mp4'),
        );
    }

    public function testParseInvalidUriThrows(): void
    {
        $parser = new ScraperGcsSourceParser('scraper-a');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid GCS URI "https://scraper-a/x.jpg".');

        $parser->parse('https://scraper-a/x.jpg');
    }

    public function testParseRejectsBucketOutsideAllowlist(): void
    {
        $parser = new ScraperGcsSourceParser('scraper-a, scraper-b');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Source bucket "evil-bucket" is not in the allowed scraper buckets.');

        $parser->parse('gs://evil-bucket/secret.json');
    }
}

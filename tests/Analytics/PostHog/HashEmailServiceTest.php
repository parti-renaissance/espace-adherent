<?php declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\HashEmailService;
use App\Analytics\PostHog\SiteContext;
use PHPUnit\Framework\TestCase;

class HashEmailServiceTest extends TestCase
{
    private const SALTS = [
        'attalpresident'      => 'attalpresident-2027',
        'parti-renaissance'   => 'parti-renaissance-2027',
        'avecgabrielattal'    => 'avecgabrielattal-2027',
        'nouvellerepublique'  => 'nouvellerepublique-2027',
    ];

    public function testKnownHashPerSite(): void
    {
        $email = 'test@example.com';
        $expected = [
            'attalpresident'      => 'c8be02d2a41f9f84e80335c10ba29dddc09d94645cfbecf81a88161d86a3eda0',
            'parti-renaissance'   => 'f1bfce1212e9adc7c7e789acc6727ef278c48618c2fb3b99580fde3c891b87ea',
            'avecgabrielattal'    => 'ebc164cb050861a4297a5e658fbfabeb3e051770bcd659ea452ec80793ee8a9d',
            'nouvellerepublique'  => '1d07c9a32f4cf1a8d2542334ec6dc7fabedf9b69487969e9c4f9909bd98ad4f1',
        ];
        foreach ($expected as $site => $hash) {
            $ctx = new SiteContext();
            $ctx->setSite($site);
            $service = new HashEmailService($ctx, self::SALTS);
            $this->assertSame($hash, $service->hash($email), "Site: $site");
        }
    }

    public function testNormalizeTrimLowercase(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $service = new HashEmailService($ctx, self::SALTS);
        $this->assertSame(
            'c8be02d2a41f9f84e80335c10ba29dddc09d94645cfbecf81a88161d86a3eda0',
            $service->hash('  Test@Example.COM '),
        );
    }

    public function testEmptyEmailThrows(): void
    {
        $ctx = new SiteContext(); $ctx->setSite('attalpresident');
        $service = new HashEmailService($ctx, self::SALTS);
        $this->expectException(\InvalidArgumentException::class);
        $service->hash('   ');
    }
}

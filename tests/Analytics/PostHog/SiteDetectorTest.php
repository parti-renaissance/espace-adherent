<?php

declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\SiteDetector;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class SiteDetectorTest extends TestCase
{
    public function testMappingKnownHostnames(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $detector = new SiteDetector($logger);

        $cases = [
            'utilisateur.parti-renaissance.fr' => 'parti-renaissance',
            'utilisateur.attalpresident.fr' => 'attalpresident',
            'utilisateur.avecgabrielattal.fr' => 'avecgabrielattal',
            'utilisateur.nouvellerepublique.fr' => 'nouvellerepublique',
        ];
        foreach ($cases as $host => $expected) {
            $request = Request::create('/', 'GET', server: ['HTTP_HOST' => $host]);
            $this->assertSame($expected, $detector->detectFromRequest($request), "Host: $host");
        }
    }

    public function testUnmappedHostnameReturnsNull(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('hors périmètre PostHog'));

        $detector = new SiteDetector($logger);
        $request = Request::create('/', 'GET', server: ['HTTP_HOST' => 'admin.attalpresident.fr']);
        $this->assertNull($detector->detectFromRequest($request));
    }

    public function testCookieConfigByMarque(): void
    {
        $this->assertSame(
            ['name' => 'ap_consent', 'domain' => '.attalpresident.fr'],
            SiteDetector::getCookieConfig('attalpresident'),
        );
        $this->assertSame(
            ['name' => 'pr_consent', 'domain' => '.parti-renaissance.fr'],
            SiteDetector::getCookieConfig('parti-renaissance'),
        );
        $this->assertSame(
            ['name' => 'aga_consent', 'domain' => '.avecgabrielattal.fr'],
            SiteDetector::getCookieConfig('avecgabrielattal'),
        );
        $this->assertSame(
            ['name' => 'nr_consent', 'domain' => '.nouvellerepublique.fr'],
            SiteDetector::getCookieConfig('nouvellerepublique'),
        );
    }
}

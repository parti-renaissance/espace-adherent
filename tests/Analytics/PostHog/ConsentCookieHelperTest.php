<?php

declare(strict_types=1);

namespace Tests\App\Analytics\PostHog;

use App\Analytics\PostHog\ConsentCookieHelper;
use App\Analytics\PostHog\SiteContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class ConsentCookieHelperTest extends TestCase
{
    public function testReadReturnsNullWhenCookieAbsent(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('attalpresident');
        $helper = new ConsentCookieHelper($ctx);
        $this->assertNull($helper->read(Request::create('/')));
    }

    public function testReadReturnsTrueForCookieValue1(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('attalpresident');
        $helper = new ConsentCookieHelper($ctx);
        $request = Request::create('/', 'GET', cookies: ['ap_consent' => '1']);
        $this->assertTrue($helper->read($request));
    }

    public function testReadReturnsFalseForCookieValue0(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('parti-renaissance');
        $helper = new ConsentCookieHelper($ctx);
        $request = Request::create('/', 'GET', cookies: ['pr_consent' => '0']);
        $this->assertFalse($helper->read($request));
    }

    public function testWriteProducesCookieWithScopedDomain(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('attalpresident');
        $helper = new ConsentCookieHelper($ctx);
        $cookie = $helper->write(true);

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertSame('ap_consent', $cookie->getName());
        $this->assertSame('1', $cookie->getValue());
        $this->assertSame('.attalpresident.fr', $cookie->getDomain());
        $this->assertSame('/', $cookie->getPath());
        $this->assertTrue($cookie->isSecure());
        $this->assertFalse($cookie->isHttpOnly());
        $this->assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    public function testWriteFalseSetsValue0(): void
    {
        $ctx = new SiteContext();
        $ctx->setSite('avecgabrielattal');
        $helper = new ConsentCookieHelper($ctx);
        $cookie = $helper->write(false);

        $this->assertSame('aga_consent', $cookie->getName());
        $this->assertSame('0', $cookie->getValue());
        $this->assertSame('.avecgabrielattal.fr', $cookie->getDomain());
    }
}

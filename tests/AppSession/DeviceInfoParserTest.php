<?php

declare(strict_types=1);

namespace Tests\App\AppSession;

use App\AppSession\DeviceInfoParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeviceInfoParserTest extends TestCase
{
    private DeviceInfoParser $parser;

    protected function setUp(): void
    {
        $this->parser = new DeviceInfoParser();
    }

    #[DataProvider('provideDesktopUserAgents')]
    public function testParseDesktopUserAgent(string $userAgent, string $expected): void
    {
        $this->assertSame($expected, $this->parser->parse($userAgent));
    }

    public static function provideDesktopUserAgents(): iterable
    {
        yield 'Safari on macOS' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Safari/605.1.15',
            'Safari (macOS)',
        ];

        yield 'Chrome on Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
            'Chrome (Windows)',
        ];

        yield 'Firefox on Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0',
            'Firefox (Windows)',
        ];

        yield 'Edge on Windows' => [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0',
            'Edge (Windows)',
        ];

        yield 'Chrome on Linux' => [
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
            'Chrome (Linux)',
        ];

        yield 'Firefox on Ubuntu' => [
            'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:148.0) Gecko/20100101 Firefox/148.0',
            'Firefox (Ubuntu)',
        ];

        yield 'Firefox on macOS' => [
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',
            'Firefox (macOS)',
        ];
    }

    #[DataProvider('provideIosUserAgents')]
    public function testParseIosUserAgent(string $userAgent, string $expected): void
    {
        $this->assertSame($expected, $this->parser->parse($userAgent));
    }

    public static function provideIosUserAgents(): iterable
    {
        yield 'iPhone Safari' => [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Mobile/15E148 Safari/604.1',
            'iPhone (iOS 17.6.1)',
        ];

        yield 'iPhone WebView' => [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
            'iPhone (iOS 18.7)',
        ];

        yield 'iPad WebView' => [
            'Mozilla/5.0 (iPad; CPU OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148',
            'iPad (iOS 18.7)',
        ];

        yield 'iPad Chrome' => [
            'Mozilla/5.0 (iPad; CPU OS 26_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/146.0.7680.40 Mobile/15E148 Safari/604.1',
            'iPad (iOS 26.3.1)',
        ];
    }

    #[DataProvider('provideAndroidUserAgents')]
    public function testParseAndroidUserAgent(string $userAgent, string $expected): void
    {
        $this->assertSame($expected, $this->parser->parse($userAgent));
    }

    public static function provideAndroidUserAgents(): iterable
    {
        yield 'Samsung specific model' => [
            'Mozilla/5.0 (Linux; Android 16; SM-A256B Build/BP2A.250605.031.A3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.159 Mobile Safari/537.36',
            'Samsung SM-A256B (Android 16)',
        ];

        yield 'Google Pixel' => [
            'Mozilla/5.0 (Linux; Android 16; Pixel 9 Build/BP4A.260205.002.A1; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.159 Mobile Safari/537.36',
            'Google Pixel 9 (Android 16)',
        ];

        yield 'Nokia' => [
            'Mozilla/5.0 (Linux; Android 12; Nokia 8.3 5G Build/SKQ1.210821.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36',
            'Nokia 8.3 5G (Android 12)',
        ];

        yield 'Honor (Huawei)' => [
            'Mozilla/5.0 (Linux; Android 14; ELI-NX9 Build/HONORELI-N39; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.120 Mobile Safari/537.36',
            'Huawei Honor ELI-NX9 (Android 14)',
        ];

        yield 'Generic Android (K device)' => [
            'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36',
            'Android (Android 10)',
        ];

        yield 'Samsung Browser on generic Android' => [
            'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/29.0 Chrome/136.0.0.0 Mobile Safari/537.36',
            'Android (Android 10)',
        ];
    }
}

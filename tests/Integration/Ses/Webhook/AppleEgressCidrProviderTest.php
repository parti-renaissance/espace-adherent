<?php

declare(strict_types=1);

namespace Tests\App\Integration\Ses\Webhook;

use App\Ses\Webhook\AppleEgressCidrProvider;
use PHPUnit\Framework\TestCase;

final class AppleEgressCidrProviderTest extends TestCase
{
    public function testReadsCidrsSkippingCommentsAndBlanks(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'cidrs');
        file_put_contents($path, "# header comment\n17.58.0.0/16\n\n2620:149::/32\n");

        try {
            self::assertSame(['17.58.0.0/16', '2620:149::/32'], new AppleEgressCidrProvider($path)->getCidrs());
        } finally {
            unlink($path);
        }
    }

    public function testMissingFileYieldsEmptyListAsSafeDefault(): void
    {
        self::assertSame([], new AppleEgressCidrProvider('/nonexistent/apple_egress_cidrs.txt')->getCidrs());
    }

    public function testEmptyPathYieldsEmptyList(): void
    {
        self::assertSame([], new AppleEgressCidrProvider('')->getCidrs());
    }
}

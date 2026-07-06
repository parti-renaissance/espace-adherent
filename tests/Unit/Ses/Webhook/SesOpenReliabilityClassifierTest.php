<?php

declare(strict_types=1);

namespace Tests\App\Unit\Ses\Webhook;

use App\Ses\Webhook\AppleEgressCidrProvider;
use App\Ses\Webhook\OpenReliability;
use App\Ses\Webhook\SesOpenReliabilityClassifier;
use PHPUnit\Framework\TestCase;

final class SesOpenReliabilityClassifierTest extends TestCase
{
    /** @var string[] */
    private const array APPLE_CIDRS = ['17.58.0.0/16', '2620:149::/32'];

    public function testAppleEgressIpv4IsUnreliable(): void
    {
        self::assertSame(OpenReliability::Unreliable, $this->classify('17.58.63.100'));
    }

    public function testAppleEgressIpv6IsUnreliable(): void
    {
        self::assertSame(OpenReliability::Unreliable, $this->classify('2620:149:af0::10'));
    }

    public function testIpOutsideAppleEgressIsReliable(): void
    {
        self::assertSame(OpenReliability::Reliable, $this->classify('203.0.113.5'));
    }

    public function testNullIpIsUnknown(): void
    {
        // No fetch IP in the event: nothing to assess, not silently counted as human.
        self::assertSame(OpenReliability::Unknown, $this->classify(null));
    }

    private function classify(?string $ipAddress): OpenReliability
    {
        $provider = $this->createStub(AppleEgressCidrProvider::class);
        $provider->method('getCidrs')->willReturn(self::APPLE_CIDRS);

        return new SesOpenReliabilityClassifier($provider)->classify($ipAddress);
    }
}

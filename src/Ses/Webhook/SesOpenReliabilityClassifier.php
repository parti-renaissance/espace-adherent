<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use Symfony\Component\HttpFoundation\IpUtils;

class SesOpenReliabilityClassifier
{
    /** @var string[] */
    private readonly array $appleEgressCidrs;

    public function __construct(AppleEgressCidrProvider $cidrProvider)
    {
        $this->appleEgressCidrs = $cidrProvider->getCidrs();
    }

    public function classify(?string $ipAddress): OpenReliability
    {
        if (null === $ipAddress) {
            return OpenReliability::Unknown;
        }

        if (IpUtils::checkIp($ipAddress, $this->appleEgressCidrs)) {
            return OpenReliability::Unreliable;
        }

        return OpenReliability::Reliable;
    }
}

<?php

declare(strict_types=1);

namespace App\AppSession;

use UAParser\Parser;
use UAParser\Result\Client;

class DeviceInfoParser
{
    private ?Parser $parser = null;

    public function parse(string $userAgent): string
    {
        $result = $this->getParser()->parse($userAgent);

        if ($this->isMobile($result, $userAgent)) {
            return $this->formatMobileDevice($result->device, $result->os);
        }

        return $this->formatDesktopDevice($result->ua, $result->os);
    }

    private function isMobile(Client $result, string $userAgent): bool
    {
        return \in_array($result->device->family, ['iPhone', 'iPad', 'iPod'], true)
            || 'iOS' === $result->os->family
            || 'Android' === $result->os->family
            || str_contains($userAgent, 'Mobile');
    }

    private function formatMobileDevice(object $device, object $os): string
    {
        if (\in_array($device->family, ['iPhone', 'iPad', 'iPod'], true)) {
            $deviceName = $device->family;
        } elseif ($device->model && 'Other' !== $device->model && 'K' !== $device->model) {
            $deviceName = $device->brand && 'Generic_Android' !== $device->brand
                ? "{$device->brand} {$device->model}"
                : $device->model;
        } elseif ($device->brand && 'Generic_Android' !== $device->brand) {
            $deviceName = $device->brand;
        } else {
            $deviceName = 'Android';
        }

        $osVersion = $os->toVersion();

        return $osVersion
            ? "{$deviceName} ({$os->family} {$osVersion})"
            : "{$deviceName} ({$os->family})";
    }

    private function formatDesktopDevice(object $browser, object $os): string
    {
        $osName = match (true) {
            str_contains($os->family, 'Mac') => 'macOS',
            str_contains($os->family, 'Windows') => 'Windows',
            str_contains($os->family, 'Ubuntu') => 'Ubuntu',
            str_contains($os->family, 'Linux') => 'Linux',
            default => $os->family,
        };

        return $osName && 'Other' !== $osName
            ? "{$browser->family} ({$osName})"
            : $browser->family;
    }

    public function getParser(): Parser
    {
        return $this->parser ??= Parser::create();
    }
}

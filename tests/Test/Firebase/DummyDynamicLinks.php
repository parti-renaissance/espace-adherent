<?php

namespace Tests\App\Test\Firebase;

use GuzzleHttp\Psr7\Response;
use Kreait\Firebase\Contract\DynamicLinks;
use Kreait\Firebase\DynamicLink;
use Kreait\Firebase\DynamicLink\DynamicLinkStatistics;

class DummyDynamicLinks implements DynamicLinks
{
    public function createUnguessableLink($url): DynamicLink
    {
        return $this->createShortLink($url);
    }

    public function createShortLink($url): DynamicLink
    {
        return DynamicLink::fromApiResponse(new Response(200, [], '{"shortLink":"https://app.fake.code/'.substr(base64_encode(random_bytes(10)), 10).'"}'));
    }

    public function createDynamicLink($actionOrParametersOrUrl, ?string $suffixType = null): DynamicLink
    {
        return $this->createShortLink('');
    }

    public function shortenLongDynamicLink($longDynamicLinkOrAction, ?string $suffixType = null): DynamicLink
    {
        return $this->createShortLink('');
    }

    public function getStatistics($dynamicLinkOrAction, ?int $durationInDays = null): DynamicLinkStatistics
    {
        return DynamicLinkStatistics::fromApiResponse(new Response(200, [], '{}'));
    }
}

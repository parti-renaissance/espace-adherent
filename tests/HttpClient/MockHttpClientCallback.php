<?php

declare(strict_types=1);

namespace Tests\App\HttpClient;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockHttpClientCallback
{
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        $body = $this->loadFixtures(parse_url($url));

        return new MockResponse($body);
    }

    private function loadFixtures(array $urlParts): ?string
    {
        if (!file_exists($fixturePath = __DIR__.'/fixtures/'.$urlParts['host'].'.json')) {
            return null;
        }

        $data = json_decode(file_get_contents($fixturePath), true);

        $query = $urlParts['query'] ?? '';

        if (isset($data[$key = $urlParts['path'].($query ? '?'.$query : '')])) {
            return json_encode($data[$key]);
        } elseif (isset($data[$urlParts['path']])) {
            return json_encode($data[$urlParts['path']]);
        }

        return json_encode($data);
    }
}

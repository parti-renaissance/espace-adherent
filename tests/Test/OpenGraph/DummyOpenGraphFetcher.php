<?php

declare(strict_types=1);

namespace Tests\App\Test\OpenGraph;

use App\OpenGraph\OpenGraphFetcher;

class DummyOpenGraphFetcher extends OpenGraphFetcher
{
    public function fetch(string $url): ?array
    {
        return [
            'site_name' => 'Dummy OpenGraph site name',
            'type' => 'Dummy OpenGraph type',
            'url' => $url,
            'title' => 'Dummy OpenGraph title',
            'description' => 'Dummy OpenGraph description',
            'image' => 'https://dummy-opengraph.com/image.jpg',
        ];
    }
}

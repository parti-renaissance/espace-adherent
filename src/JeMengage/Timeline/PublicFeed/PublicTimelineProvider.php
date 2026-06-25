<?php

declare(strict_types=1);

namespace App\JeMengage\Timeline\PublicFeed;

use App\Entity\Geo\Zone;
use App\Entity\Timeline\TimelineFeed;
use App\Repository\Timeline\TimelineFeedRepository;

class PublicTimelineProvider
{
    private const int PAGE_SIZE = 10;

    public function __construct(
        private readonly TimelineFeedRepository $repository,
        private readonly PublicTimelineSanitizer $sanitizer,
    ) {
    }

    /**
     * @return array{hits: array<int, array<string, mixed>>, nbHits: int, page: int, nbPages: int, hitsPerPage: int}
     */
    public function findItems(int $page, ?Zone $zone = null): array
    {
        $rows = $this->repository->findPublicFeed($page, self::PAGE_SIZE, $zone);
        $total = $this->repository->countPublicFeed($zone);

        $hits = array_map(fn (TimelineFeed $row): array => $this->sanitizer->sanitize($row->display), $rows);

        return [
            'hits' => $hits,
            'nbHits' => \count($hits),
            'page' => $page,
            'nbPages' => (int) ceil($total / self::PAGE_SIZE),
            'hitsPerPage' => self::PAGE_SIZE,
        ];
    }
}

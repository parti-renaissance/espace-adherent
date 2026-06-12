<?php

declare(strict_types=1);

namespace App\Entity\Timeline;

use App\Entity\EntityIdentityTrait;
use App\Repository\Timeline\TimelineFeedRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flat local mirror of a JeMengage timeline feed item, kept in sync asynchronously.
 *
 * One row = one timeline item, identified by the source entity UUID (unique). The canonical model
 * separates three responsibilities: the app display contract (`display`, the unchanged normalizer
 * record), the targeting audience (`audience`, a {include, exclude} model), and operator-tunable
 * indexer signals (`authorImportance`). `authorImportance` is operator-owned: it has a DB default
 * and is never written by the sync (excluded from TimelineFeedWriter), so a re-upsert preserves it.
 *
 * Rows are written via raw DBAL (TimelineFeedWriter) and only ever hydrated by Doctrine on read.
 */
#[ORM\Entity(repositoryClass: TimelineFeedRepository::class)]
#[ORM\Index(columns: ['publication_date'])]
class TimelineFeed
{
    use EntityIdentityTrait;

    #[ORM\Column(length: 50)]
    public string $type;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $publicationDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $eventDate = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $audience = null;

    #[ORM\Column(type: 'json')]
    public array $display;

    #[ORM\Column(type: 'smallint', options: ['default' => 1])]
    public int $authorImportance = 1;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $updatedAt;
}

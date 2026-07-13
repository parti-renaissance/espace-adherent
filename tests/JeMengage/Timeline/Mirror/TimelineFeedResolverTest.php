<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Mirror;

use App\DataFixtures\ORM\LoadPollData;
use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Entity\Poll\Poll;
use App\JeMengage\Timeline\Mirror\TimelineFeedResolver;
use App\JeMengage\Timeline\TimelineFeedTypeEnum;
use Tests\App\AbstractKernelTestCase;

class TimelineFeedResolverTest extends AbstractKernelTestCase
{
    private ?TimelineFeedResolver $resolver = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = $this->get(TimelineFeedResolver::class);
    }

    protected function tearDown(): void
    {
        $this->resolver = null;

        parent::tearDown();
    }

    public function testResolvesPublishedNewsToTheCanonicalModel(): void
    {
        /** @var News $news */
        $news = $this->getRepository(News::class)->findOneBy([]);
        $news->setPublished(true);

        $document = $this->resolver->resolve($news);

        self::assertNotNull($document);
        self::assertFalse($document->isRemoval());
        self::assertTrue($news->getUuid()->equals($document->objectId));
        self::assertSame('news', $document->type);
        self::assertInstanceOf(\DateTimeImmutable::class, $document->publicationDate);
        // display = the unchanged Algolia record: objectID (as string) + the normalizer output.
        self::assertSame($document->objectId->toRfc4122(), $document->display['objectID']);
        self::assertArrayHasKey('title', $document->display);
    }

    public function testResolvesPublishedPollToTheCanonicalModel(): void
    {
        /** @var Poll $poll */
        $poll = $this->getRepository(Poll::class)->findOneBy(['uuid' => LoadPollData::POLL_01_UUID]);

        $document = $this->resolver->resolve($poll);

        self::assertNotNull($document);
        self::assertFalse($document->isRemoval());
        self::assertTrue($poll->getUuid()->equals($document->objectId));
        self::assertSame(TimelineFeedTypeEnum::POLL, $document->type);
        self::assertInstanceOf(\DateTimeImmutable::class, $document->publicationDate);
        self::assertEquals($poll->getFinishAt(), $document->eventDate);
        self::assertSame($poll->getStartAt()->format('c'), $document->display['begin_at']);
        self::assertSame($document->objectId->toRfc4122(), $document->display['objectID']);
        self::assertSame('Plutôt thé ou café ?', $document->display['title']);
    }

    public function testReturnsRemovalMarkerForUnpublishedNews(): void
    {
        /** @var News $news */
        $news = $this->getRepository(News::class)->findOneBy([]);
        $news->setPublished(false);

        $document = $this->resolver->resolve($news);

        self::assertNotNull($document);
        self::assertTrue($document->isRemoval(), 'A non-indexable entity yields a removal marker (null display).');
        self::assertNull($document->display);
        self::assertTrue($news->getUuid()->equals($document->objectId));
    }

    public function testReturnsNullForUnsupportedEntity(): void
    {
        $adherent = $this->getRepository(Adherent::class)->findOneBy([]);

        self::assertNull($this->resolver->resolve($adherent));
        self::assertFalse($this->resolver->supports($adherent));
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\JeMengage\Timeline\Indexer;

use App\Entity\Adherent;
use App\JeMengage\Timeline\Indexer\TimelineSessionResolver;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TimelineSessionResolverTest extends TestCase
{
    public function testReturnsAppSessionIdAsIsWithoutTouchingCache(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects(self::never())->method('getItem');
        $cache->expects(self::never())->method('save');

        $resolver = new TimelineSessionResolver($cache, new NullLogger());

        self::assertSame('app-sess', $resolver->resolve($this->adherent(624), 'app-sess'));
    }

    public function testGeneratesStableFallbackCursorAcrossRequestsForTheSameUser(): void
    {
        $resolver = new TimelineSessionResolver(new ArrayAdapter(), new NullLogger());
        $user = $this->adherent(624);

        $first = $resolver->resolve($user, null);
        $second = $resolver->resolve($user, null);

        self::assertNotNull($first);
        // Stable across requests: it is the indexer cursor; a new id each call would reset the seen-set.
        self::assertSame($first, $second);
    }

    public function testReturnsNullWhenFallbackCannotBePersisted(): void
    {
        // Cache backend down: save() fails. A session_id regenerated on every request would reset the
        // indexer cursor and return duplicates, so the resolver returns null -> the provider degrades to a
        // safe single page rather than paginating with a broken cursor.
        $item = $this->createStub(CacheItemInterface::class);
        $item->method('isHit')->willReturn(false);
        $item->method('set')->willReturnSelf();
        $item->method('expiresAfter')->willReturnSelf();

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->expects(self::once())->method('getItem')->willReturn($item);
        $cache->expects(self::once())->method('save')->willReturn(false);

        $resolver = new TimelineSessionResolver($cache, new NullLogger());

        self::assertNull($resolver->resolve($this->adherent(624), null));
    }

    private function adherent(int $id): Adherent
    {
        $user = new \ReflectionClass(Adherent::class)->newInstanceWithoutConstructor();
        new \ReflectionProperty(Adherent::class, 'id')->setValue($user, $id);

        return $user;
    }
}

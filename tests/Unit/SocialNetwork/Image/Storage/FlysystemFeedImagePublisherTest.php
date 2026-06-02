<?php

declare(strict_types=1);

namespace Tests\App\Unit\SocialNetwork\Image\Storage;

use App\SocialNetwork\Image\Storage\FlysystemFeedImagePublisher;
use App\Storage\Gcs\ScraperGcsSourceParser;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

class FlysystemFeedImagePublisherTest extends TestCase
{
    private const string PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC';
    private const int OVERSIZED = 15 * 1024 * 1024 + 1;

    public function testExpectedPathIsDeterministicAndKeepsSafeSourceExtension(): void
    {
        $publisher = $this->createPublisher($this->createStub(StorageClient::class), $this->createStub(FilesystemOperator::class));

        $uri = 'gs://scraper-a/bronze/twitter/1/media/0.png';

        self::assertSame('social-feed/'.sha1($uri).'.png', $publisher->expectedPath($uri));
        self::assertSame($publisher->expectedPath($uri), $publisher->expectedPath($uri));
    }

    public function testExpectedPathFallsBackToJpgForUnsafeExtension(): void
    {
        $publisher = $this->createPublisher($this->createStub(StorageClient::class), $this->createStub(FilesystemOperator::class));

        $uri = 'gs://scraper-a/bronze/1/0.svg';

        self::assertSame('social-feed/'.sha1($uri).'.jpg', $publisher->expectedPath($uri));
    }

    public function testPublishRejectsBucketOutsideAllowlistWithoutAnyIo(): void
    {
        $storage = $this->createMock(StorageClient::class);
        $storage->expects(self::never())->method('bucket');
        $public = $this->createMock(FilesystemOperator::class);
        $public->expects(self::never())->method('write');

        $this->expectException(\InvalidArgumentException::class);

        $this->createPublisher($storage, $public)->publish('gs://evil-bucket/secret.json');
    }

    public function testPublishSkipsDownloadWhenAlreadyExists(): void
    {
        $uri = 'gs://scraper-a/bronze/1/0.jpg';
        $storage = $this->createMock(StorageClient::class);
        $storage->expects(self::never())->method('bucket');

        $public = $this->createMock(FilesystemOperator::class);
        $public->expects(self::once())->method('fileExists')->with('social-feed/'.sha1($uri).'.jpg')->willReturn(true);
        $public->expects(self::never())->method('write');

        $published = $this->createPublisher($storage, $public)->publish($uri);

        self::assertSame('social-feed/'.sha1($uri).'.jpg', $published->path);
        self::assertNull($published->width);
        self::assertNull($published->height);
    }

    public function testPublishRejectsObjectExceedingSizeMetadataWithoutDownloading(): void
    {
        $object = $this->createMock(StorageObject::class);
        $object->method('info')->willReturn(['size' => (string) self::OVERSIZED]);
        $object->expects(self::never())->method('downloadAsString');

        $public = $this->createMock(FilesystemOperator::class);
        $public->expects(self::once())->method('fileExists')->willReturn(false);
        $public->expects(self::never())->method('write');

        $this->expectException(\InvalidArgumentException::class);

        $this->createPublisher($this->storageReturning($object), $public)->publish('gs://scraper-a/bronze/1/0.jpg');
    }

    public function testPublishRejectsNonImageContent(): void
    {
        $public = $this->createMock(FilesystemOperator::class);
        $public->expects(self::once())->method('fileExists')->willReturn(false);
        $public->expects(self::never())->method('write');

        $this->expectException(\InvalidArgumentException::class);

        $this->createPublisher($this->storageReturningContent('this is plain text, not an image'), $public)->publish('gs://scraper-a/bronze/1/0.jpg');
    }

    public function testPublishRejectsSvgContent(): void
    {
        $svg = '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $public = $this->createMock(FilesystemOperator::class);
        $public->expects(self::once())->method('fileExists')->willReturn(false);
        $public->expects(self::never())->method('write');

        $this->expectException(\InvalidArgumentException::class);

        $this->createPublisher($this->storageReturningContent($svg), $public)->publish('gs://scraper-a/bronze/1/0.svg');
    }

    public function testPublishDownloadsAndWritesImage(): void
    {
        $uri = 'gs://scraper-a/bronze/1/0.png';
        $content = base64_decode(self::PNG_1X1);

        $public = $this->createMock(FilesystemOperator::class);
        $public->expects(self::once())->method('fileExists')->willReturn(false);
        $public->expects(self::once())->method('write')->with('social-feed/'.sha1($uri).'.png', $content);

        $published = $this->createPublisher($this->storageReturningContent($content), $public)->publish($uri);

        self::assertSame('social-feed/'.sha1($uri).'.png', $published->path);
        self::assertSame(1, $published->width);
        self::assertSame(1, $published->height);
    }

    private function createPublisher(StorageClient $storage, FilesystemOperator $public): FlysystemFeedImagePublisher
    {
        return new FlysystemFeedImagePublisher(new ScraperGcsSourceParser('scraper-a, scraper-b'), $storage, $public);
    }

    private function storageReturningContent(string $content): StorageClient
    {
        $object = $this->createMock(StorageObject::class);
        $object->method('info')->willReturn(['size' => (string) \strlen($content)]);
        $object->expects(self::once())->method('downloadAsString')->willReturn($content);

        return $this->storageReturning($object);
    }

    private function storageReturning(StorageObject $object): StorageClient
    {
        $bucket = $this->createMock(Bucket::class);
        $bucket->expects(self::once())->method('object')->willReturn($object);

        $storage = $this->createMock(StorageClient::class);
        $storage->expects(self::once())->method('bucket')->with('scraper-a')->willReturn($bucket);

        return $storage;
    }
}

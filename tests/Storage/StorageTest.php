<?php

namespace Tests\App\Storage;

use App\Storage\Exception\FileExistsException;
use App\Storage\Exception\FileNotFoundException;
use App\Storage\Storage;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    public function testHas(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('has')
            ->with('foo/bar.txt')
            ->willReturn(true)
        ;

        $storage = $this->createStorage($filesystem);

        self::assertTrue($storage->has('foo/bar.txt'));
    }

    public function testGetMimetype(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('getMimetype')
            ->with('foo/bar.txt')
            ->willReturn('text/plain')
        ;

        $storage = $this->createStorage($filesystem);

        self::assertSame('text/plain', $storage->getMimetype('foo/bar.txt'));
    }

    public function testGetMimetypeFileNotFound(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('getMimeType')
            ->with('foo/bar.txt')
            ->willThrowException($this->createFileNotFoundException('foo/bar.txt'))
        ;

        $storage = $this->createStorage($filesystem);

        self::expectFileNotFoundException('foo/bar.txt');

        $storage->getMimetype('foo/bar.txt');
    }

    public function testRead(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('read')
            ->with('foo/bar.txt')
            ->willReturn('Lorem ipsum')
        ;

        $storage = $this->createStorage($filesystem);

        self::assertSame('Lorem ipsum', $storage->read('foo/bar.txt'));
    }

    public function testReadFileNotFound(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('read')
            ->with('foo/bar.txt')
            ->willThrowException($this->createFileNotFoundException('foo/bar.txt'))
        ;

        $storage = $this->createStorage($filesystem);

        self::expectFileNotFoundException('foo/bar.txt');

        $storage->read('foo/bar.txt');
    }

    public function testReadStream(): void
    {
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Lorem ipsum');
        rewind($resource);

        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('readStream')
            ->with('foo/bar.txt')
            ->willReturn($resource)
        ;

        $storage = $this->createStorage($filesystem);

        $stream = $storage->readStream('foo/bar.txt');

        self::assertIsResource($stream);
        self::assertSame('Lorem ipsum', stream_get_contents($stream));
    }

    public function testReadStreamFileNotFound(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('readStream')
            ->with('foo/bar.txt')
            ->willThrowException($this->createFileNotFoundException('foo/bar.txt'))
        ;

        $storage = $this->createStorage($filesystem);

        self::expectFileNotFoundException('foo/bar.txt');

        $storage->readStream('foo/bar.txt');
    }

    public function testPut(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('put')
            ->with('foo/bar.txt', 'Lorem ipsum')
            ->willReturn(true)
        ;

        $storage = $this->createStorage($filesystem);

        self::assertTrue($storage->put('foo/bar.txt', 'Lorem ipsum'));
    }

    public function testCopy(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('copy')
            ->with('foo/bar.txt', 'foo/bar_new.txt')
            ->willReturn(true)
        ;

        $storage = $this->createStorage($filesystem);

        self::assertTrue($storage->copy('foo/bar.txt', 'foo/bar_new.txt'));
    }

    public function testCopySourceNotFound(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('copy')
            ->with('foo/bar.txt', 'foo/bar_new.txt')
            ->willThrowException($this->createFileNotFoundException('foo/bar.txt'))
        ;

        $storage = $this->createStorage($filesystem);

        self::expectFileNotFoundException('foo/bar.txt');

        $storage->copy('foo/bar.txt', 'foo/bar_new.txt');
    }

    public function testCopyDestinationExists(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('copy')
            ->with('foo/bar.txt', 'foo/bar_new.txt')
            ->willThrowException($this->createFileExistsException('foo/bar_new.txt'))
        ;

        $storage = $this->createStorage($filesystem);

        self::expectFileExistsException('foo/bar_new.txt');

        $storage->copy('foo/bar.txt', 'foo/bar_new.txt');
    }

    public function testDelete(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with('foo/bar.txt')
            ->willReturn(true)
        ;

        $storage = $this->createStorage($filesystem);

        self::assertTrue($storage->delete('foo/bar.txt'));
    }

    public function testDeleteFileNotFound(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('delete')
            ->with('foo/bar.txt')
            ->willThrowException($this->createFileNotFoundException('foo/bar.txt'))
        ;

        $storage = $this->createStorage($filesystem);

        self::expectFileNotFoundException('foo/bar.txt');

        $storage->delete('foo/bar.txt');
    }

    public function testListContents(): void
    {
        $filesystem = $this->createFilesystemMock();
        $filesystem
            ->expects(self::once())
            ->method('listContents')
            ->with('foo', false)
            ->willReturn(['foo/bar1.txt', 'foo/bar2.txt'])
        ;

        $storage = $this->createStorage($filesystem);

        self::assertSame(['foo/bar1.txt', 'foo/bar2.txt'], $storage->listContents('foo'));
    }

    private function createStorage(FilesystemInterface $filesystem): Storage
    {
        return new Storage($filesystem);
    }

    /**
     * @return MockObject|FilesystemInterface
     */
    private function createFilesystemMock(): MockObject
    {
        return $this->createMock(FilesystemInterface::class);
    }

    private function createFileNotFoundException(string $path): FileNotFoundException
    {
        return new FileNotFoundException($path);
    }

    private function createFileExistsException(string $path): FileExistsException
    {
        return new FileExistsException($path);
    }

    private function expectFileNotFoundException(string $path): void
    {
        self::expectException(FileNotFoundException::class);
        self::expectExceptionMessage("File not found at path: $path");
    }

    private function expectFileExistsException(string $path): void
    {
        self::expectException(FileExistsException::class);
        self::expectExceptionMessage("File already exists at path: $path");
    }
}

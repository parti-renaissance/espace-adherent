<?php

namespace Tests\App\EventListener;

use App\Entity\Mooc\AttachmentFile;
use App\EntityListener\FileListener;
use League\Flysystem\FilesystemInterface;
use League\Glide\Server;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileListenerTest extends WebTestCase
{
    private $entityFileListener;

    public function testProcessFile(): void
    {
        $entityFile = new AttachmentFile();
        $entityFile->setFile(new UploadedFile(__FILE__, __FILE__));

        $this->entityFileListener->processFile($entityFile);

        self::assertNotNull($entityFile->getPath());
        self::assertNull($entityFile->getFile());
    }

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityFileListener = new FileListener(
            static::$container->get(FilesystemInterface::class),
            static::$container->get(Server::class)
        );
    }
}

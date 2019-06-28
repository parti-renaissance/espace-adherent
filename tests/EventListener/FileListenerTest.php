<?php

namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\Mooc\AttachmentFile;
use AppBundle\EntityListener\FileListener;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileListenerTest extends WebTestCase
{
    private $entityFileListener;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();
        $this->entityFileListener = new FileListener(
            $container->get(Filesystem::class),
            $container->get(Server::class)
        );
    }

    public function testProcessFile(): void
    {
        $entityFile = new AttachmentFile();
        $entityFile->setFile(new UploadedFile(__FILE__, __FILE__));

        $this->entityFileListener->processFile($entityFile);

        self::assertNotNull($entityFile->getPath());
        self::assertNull($entityFile->getFile());
    }
}

<?php

declare(strict_types=1);

namespace App\Filesystem;

use App\Entity\Filesystem\File;
use App\Repository\Filesystem\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManager
{
    private $storage;
    private $entityManager;
    private $repository;

    public function __construct(
        FilesystemOperator $defaultStorage,
        EntityManagerInterface $entityManager,
        FileRepository $repository,
    ) {
        $this->storage = $defaultStorage;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function update(File $file): void
    {
        if ($file->getExternalLink()) {
            $file->markAsLink();
            $file->setOriginalFilename(null);
            $file->setExtension(null);
            $file->setMimeType(null);
            $file->setSize(null);
        }

        if ($uploadedFile = $file->getFile()) {
            $file->markAsFile();
            $file->setOriginalFilename($uploadedFile->getClientOriginalName());
            $file->setExtension($uploadedFile->getClientOriginalExtension());
            $file->setMimeType($uploadedFile->getMimeType());
            $file->setSize($uploadedFile->getSize());
        }
    }

    public function upload(File $file): void
    {
        if (!$file->getFile() instanceof UploadedFile) {
            throw new \RuntimeException(\sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $this->storage->write($file->getPath(), file_get_contents($file->getFile()->getPathname()));
    }

    public function remove(File $file): void
    {
        if ($file->isDir()) {
            $children = $this->repository->findBy(['parent' => $file]);

            foreach ($children as $child) {
                $this->remove($child);
            }
        }

        $this->entityManager->remove($file);
        $this->removeFromStorage($file);

        $this->entityManager->flush();
    }

    public function removeFromStorage(File $file): void
    {
        $filepath = $file->getPath();

        if ($this->storage->has($filepath)) {
            $this->storage->delete($filepath);
        }
    }
}

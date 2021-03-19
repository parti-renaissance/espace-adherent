<?php

namespace App\Jecoute;

use App\Entity\Jecoute\Region;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegionManager
{
    private $storage;

    public function __construct(FilesystemInterface $storage)
    {
        $this->storage = $storage;
    }

    public function handleFile(Region $region): void
    {
        $filepath = $region->getBannerPathWithDirectory();

        if ($region->getRemoveBanner() && $this->storage->has($filepath)) {
            $this->storage->delete($filepath);
            $region->removeBanner();

            return;
        }

        $this->uploadLogo($region);
        $this->uploadBanner($region);
    }

    public function uploadLogo(Region $region): void
    {
        $uploadedFile = $region->getLogoFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $region->setLogoFromUploadedFile();

        $this->storage->put(
            $region->getLogoPathWithDirectory(),
            file_get_contents($uploadedFile->getPathname()),
            ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
        );
    }

    public function uploadBanner(Region $region): void
    {
        $uploadedFile = $region->getBannerFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $region->setBannerFromUploadedFile();

        $this->storage->put(
            $region->getBannerPathWithDirectory(),
            file_get_contents($uploadedFile->getPathname()),
            ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
        );
    }

    public function removeBanner(Region $region): void
    {
        if ($region->hasBannerUploaded()) {
            $filePath = $region->getBannerPathWithDirectory();

            if ($this->storage->has($filePath)) {
                $this->storage->delete($filePath);
            }
        }
    }
}

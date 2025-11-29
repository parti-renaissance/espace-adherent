<?php

declare(strict_types=1);

namespace App\Jecoute;

use App\Entity\Jecoute\Region;
use League\Flysystem\Config;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegionManager
{
    private $storage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    public function handleFile(Region $region): void
    {
        $filepath = $region->getBannerPathWithDirectory();

        if ($region->getRemoveBannerFile() && $this->storage->has($filepath)) {
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
            throw new \RuntimeException(\sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $region->setLogoFromUploadedFile();

        $this->storage->write(
            $region->getLogoPathWithDirectory(),
            file_get_contents($uploadedFile->getPathname()),
            [Config::OPTION_VISIBILITY => Visibility::PUBLIC]
        );
    }

    public function uploadBanner(Region $region): void
    {
        $uploadedFile = $region->getBannerFile();

        if (null === $uploadedFile) {
            return;
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException(\sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $region->setBannerFromUploadedFile();

        $this->storage->write(
            $region->getBannerPathWithDirectory(),
            file_get_contents($uploadedFile->getPathname()),
            [Config::OPTION_VISIBILITY => Visibility::PUBLIC]
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

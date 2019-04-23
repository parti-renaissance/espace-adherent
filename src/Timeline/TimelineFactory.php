<?php

namespace AppBundle\Timeline;

use AppBundle\Content\MediaFactory;
use AppBundle\Entity\Media;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use AppBundle\Repository\MediaRepository;
use Cocur\Slugify\Slugify;
use League\Flysystem\Filesystem as Storage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class TimelineFactory
{
    private $filesystem;
    private $mediaFactory;
    private $mediaRepository;
    private $slugifier;
    private $storage;

    public function __construct(
        MediaFactory $mediaFactory,
        MediaRepository $mediaRepository,
        Slugify $slugifier,
        Storage $storage,
        Filesystem $filesystem
    ) {
        $this->mediaFactory = $mediaFactory;
        $this->mediaRepository = $mediaRepository;
        $this->slugifier = $slugifier;
        $this->storage = $storage;
        $this->filesystem = $filesystem;

        $this->slugifier->activateRuleSet('default');
    }

    public function createProfile(string $title, string $description): Profile
    {
        return new Profile($title, $this->slugify($title), $description);
    }

    public function createTheme(string $title, string $description, string $imageUrl, bool $isFeatured = false): Theme
    {
        $theme = new Theme($title, $this->slugify($title), $description, $isFeatured);

        $theme->setMedia($this->createMedia("Timeline - Thème $title", $imageUrl));

        return $theme;
    }

    private function createMedia(string $name, string $path): Media
    {
        if ($media = $this->mediaRepository->findOneByName($name)) {
            return $media;
        }

        if (empty($path)) {
            throw new \InvalidArgumentException(sprintf('Can not create a media for "%s" with no file path.', $name));
        }

        $mediaPath = sprintf('timeline_macron/%s.jpg', $this->slugify($name));
        $temporaryFilename = sprintf('%s/%s', sys_get_temp_dir(), $mediaPath);

        $this->filesystem->copy($path, $temporaryFilename);

        $mediaFile = new File($temporaryFilename);

        if (!$this->storage->put('images/'.$mediaPath, file_get_contents($mediaFile->getPathname()))) {
            throw new \RuntimeException(sprintf('Image "%s" can\'t be uploaded on storage. (%s)', $name, $path));
        }

        return $this->mediaFactory->createFromFile($name, $mediaPath, $mediaFile);
    }

    private function slugify(string $string): string
    {
        return $this->slugifier->slugify($string);
    }
}

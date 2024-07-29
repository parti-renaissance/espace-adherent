<?php

namespace App\Timeline;

use App\Content\MediaFactory;
use App\Entity\Media;
use App\Entity\Timeline\Profile;
use App\Entity\Timeline\ProfileTranslation;
use App\Entity\Timeline\Theme;
use App\Entity\Timeline\ThemeTranslation;
use App\Repository\MediaRepository;
use Cocur\Slugify\SlugifyInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\File;

class TimelineFactory
{
    private $mediaFactory;
    private $mediaRepository;
    private $slugifier;
    private $storage;

    public function __construct(
        MediaFactory $mediaFactory,
        MediaRepository $mediaRepository,
        SlugifyInterface $slugifier,
        FilesystemOperator $defaultStorage
    ) {
        $this->mediaFactory = $mediaFactory;
        $this->mediaRepository = $mediaRepository;
        $this->slugifier = $slugifier;
        $this->storage = $defaultStorage;

        $this->slugifier->activateRuleSet('default');
    }

    public function createProfile(string $title, string $description): Profile
    {
        $profile = new Profile();
        /** @var ProfileTranslation $translation */
        $translation = $profile->translate('fr', false);

        $translation->setTitle($title);
        $translation->setSlug($this->slugify($title));
        $translation->setDescription($description);

        return $profile;
    }

    public function createTheme(string $title, string $description, string $imageUrl, bool $isFeatured = false): Theme
    {
        $theme = new Theme($isFeatured);

        /** @var ThemeTranslation $translation */
        $translation = $theme->translate('fr', false);

        $translation->setTitle($title);
        $translation->setSlug($this->slugify($title));
        $translation->setDescription($description);

        $theme->setMedia($this->createMedia("Timeline - Thème $title", $imageUrl));

        return $theme;
    }

    private function createMedia(string $name, string $path): Media
    {
        if ($media = $this->mediaRepository->findOneByName($name)) {
            return $media;
        }

        if (empty($path)) {
            throw new \InvalidArgumentException(\sprintf('Can not create a media for "%s" with no file path.', $name));
        }

        $mediaPath = \sprintf('timeline_macron/%s.jpg', $this->slugify($name));
        $temporaryFilename = \sprintf('%s/%s', sys_get_temp_dir(), $mediaPath);

        $this->storage->copy($path, $temporaryFilename);

        $mediaFile = new File($temporaryFilename);

        if (!$this->storage->write('images/'.$mediaPath, file_get_contents($mediaFile->getPathname()))) {
            throw new \RuntimeException(\sprintf('Image "%s" can\'t be uploaded on storage. (%s)', $name, $path));
        }

        return $this->mediaFactory->createFromFile($name, $mediaPath, $mediaFile);
    }

    private function slugify(string $string): string
    {
        return $this->slugifier->slugify($string);
    }
}

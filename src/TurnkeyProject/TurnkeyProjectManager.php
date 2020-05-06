<?php

namespace App\TurnkeyProject;

use App\Entity\TurnkeyProject;
use App\Repository\TurnkeyProjectRepository;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TurnkeyProjectManager
{
    private $storage;

    /**
     * @var Server
     */
    private $glide;

    /**
     * @var TurnkeyProjectRepository
     */
    private $turnkeyProjectRepository;

    public function __construct(TurnkeyProjectRepository $turnkeyProjectRepository, Filesystem $storage)
    {
        $this->storage = $storage;
        $this->turnkeyProjectRepository = $turnkeyProjectRepository;
    }

    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }

    /**
     * Uploads and saves the turnkey project image.
     */
    public function saveImage(TurnkeyProject $turnkeyProject): void
    {
        if (!$turnkeyProject->getImage() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The image must be an instance of %s', UploadedFile::class));
        }

        // Clears the old image if needed
        if (null !== $turnkeyProject->getImageName() && $oldImagePath = $turnkeyProject->getImagePath()) {
            $this->storage->delete($oldImagePath);
        }

        $turnkeyProject->setImageName($turnkeyProject->getImage());
        $path = $turnkeyProject->getImagePath();

        // Uploads the file : creates or updates if exists
        $this->storage->put($path, file_get_contents($turnkeyProject->getImage()->getPathname()));

        // Clears the cache file
        $this->glide->deleteCache($path);
    }

    /**
     * Removes the turnkey project image.
     */
    public function removeImage(TurnkeyProject $turnkeyProject): void
    {
        if (null === $turnkeyProject->getImageName()) {
            throw new \RuntimeException('This Turnkey Project does not contain an image.');
        }

        $path = $turnkeyProject->getImagePath();

        // Deletes the file
        $this->storage->delete($path);

        // Clears the cache file
        $this->glide->deleteCache($path);

        $turnkeyProject->setImageName(null);
    }

    public function countProjects(): int
    {
        return $this->turnkeyProjectRepository->countProjects();
    }
}

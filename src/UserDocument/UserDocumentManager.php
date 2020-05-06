<?php

namespace App\UserDocument;

use App\Entity\UserDocument;
use Doctrine\Common\Persistence\ObjectManager;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDocumentManager
{
    private $validator;
    private $manager;
    private $storage;

    public function __construct(ValidatorInterface $validator, ObjectManager $manager, Filesystem $storage)
    {
        $this->validator = $validator;
        $this->manager = $manager;
        $this->storage = $storage;
    }

    public function createAndSave(UploadedFile $uploadedFile, string $type): UserDocument
    {
        $document = UserDocument::createFromUploadedFile($uploadedFile);
        $document->setType($type);

        $errors = $this->validator->validate($document);

        if (\count($errors) > 0) {
            $errMsg = '';
            foreach ($errors as $error) {
                $errMsg .= $error->getMessage().' ';
            }

            throw new ValidatorException($errMsg);
        }

        $this->manager->persist($document);
        $this->manager->flush();

        $this->saveToStorage($document->getPath(), $uploadedFile->getPathname());

        return $document;
    }

    public function saveToStorage(string $path, string $uploadedFilePath): void
    {
        $this->storage->put($path, file_get_contents($uploadedFilePath));
    }

    public function getContent(UserDocument $document): string
    {
        return $this->storage->read($document->getPath());
    }

    public function removeUnusedDocuments(iterable $documents): void
    {
        foreach ($documents as $document) {
            $this->removeIfUnused($document);
        }
    }

    public function removeIfUnused(UserDocument $document): void
    {
        $isUsed = $this->manager->getRepository(UserDocument::class)->checkIfDocumentIsUsed($document);

        if (!$isUsed) {
            $path = $document->getPath();
            $this->manager->remove($document);
            $this->manager->flush();
            $this->removeFromStorage($path);
        }
    }

    private function removeFromStorage(string $path): void
    {
        if ($this->storage->has($path)) {
            $this->storage->delete($path);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\UserDocument;

use App\Entity\UserDocument;
use App\Scope\Scope;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserDocumentManager
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $manager,
        private readonly FilesystemOperator $defaultStorage,
    ) {
    }

    public function createAndSave(UploadedFile $uploadedFile, string $type, ?Scope $scope): UserDocument
    {
        $document = UserDocument::createFromUploadedFile($uploadedFile);
        $document->setType($type);

        if ($scope) {
            $document->updateFromScope($scope);
        }

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
        $this->defaultStorage->write($path, file_get_contents($uploadedFilePath));
    }

    public function getContent(UserDocument $document): string
    {
        return $this->defaultStorage->read($document->getPath());
    }

    public function removeUnusedDocuments(iterable $documents): void
    {
        foreach ($documents as $document) {
            $this->removeIfUnused($document);
        }
    }

    public function removeIfUnused(UserDocument $document): void
    {
        $path = $document->getPath();
        $this->manager->remove($document);
        $this->manager->flush();
        $this->removeFromStorage($path);
    }

    private function removeFromStorage(string $path): void
    {
        if ($this->defaultStorage->has($path)) {
            $this->defaultStorage->delete($path);
        }
    }
}

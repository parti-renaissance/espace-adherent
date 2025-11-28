<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface UserDocumentInterface
{
    public function addDocument(UserDocument $document): void;

    public function removeDocument(UserDocument $document): void;

    public function getDocuments(): Collection;

    public function setDocuments(Collection $documents): void;

    public function getContentContainingDocuments(): string;

    public function getFieldContainingDocuments(): string;
}

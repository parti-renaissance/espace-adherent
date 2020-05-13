<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

trait UserDocumentTrait
{
    /**
     * @var Collection
     */
    protected $documents;

    public function addDocument(UserDocument $document): void
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }
    }

    public function removeDocument(UserDocument $document): void
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
        }
    }

    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function setDocuments(Collection $documents): void
    {
        $this->documents = $documents;
    }
}

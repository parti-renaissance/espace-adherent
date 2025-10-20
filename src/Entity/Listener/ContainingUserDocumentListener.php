<?php

namespace App\Entity\Listener;

use App\Entity\UserDocument;
use App\Entity\UserDocumentInterface;
use App\UserDocument\UserDocumentManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Ramsey\Uuid\Uuid;

class ContainingUserDocumentListener
{
    private $documentManager;
    private $patternUuid;
    private $documentUuidsToRemove;

    public function __construct(UserDocumentManager $documentManager, string $patternUuid)
    {
        $this->documentManager = $documentManager;
        $this->patternUuid = '/'.$patternUuid.'/';
    }

    public function postUpdate(UserDocumentInterface $object, LifecycleEventArgs $args): void
    {
        // postUpdate is called inside flush()
        // Add this to be sure that all previous modifications were executed on database
        $args->getObjectManager()->flush();
        if ($this->documentUuidsToRemove) {
            $this->documentManager->removeUnusedDocuments($this->documentUuidsToRemove);
        }
    }

    public function preRemove(UserDocumentInterface $object, LifecycleEventArgs $args): void
    {
        $this->documentUuidsToRemove = $object->getDocuments()->toArray();
    }

    public function postRemove(UserDocumentInterface $object, LifecycleEventArgs $args): void
    {
        if ($this->documentUuidsToRemove) {
            $this->documentManager->removeUnusedDocuments($this->documentUuidsToRemove);
        }
    }

    public function prePersist(UserDocumentInterface $object, LifecycleEventArgs $args): void
    {
        preg_match_all($this->patternUuid, $object->getContentContainingDocuments(), $documentUuids);
        $documentUuids = $this->prepareUuidsArray($documentUuids);

        $entityManager = $args->getObjectManager();
        if ($documents = $entityManager->getRepository(UserDocument::class)->findBy(['uuid' => $documentUuids])) {
            $object->setDocuments(new ArrayCollection($documents));
        }
    }

    public function preUpdate(UserDocumentInterface $object, LifecycleEventArgs $args): void
    {
        if (!$args->hasChangedField($field = $object->getFieldContainingDocuments())) {
            return;
        }

        $oldDocumentUuids = $object->getDocuments();
        preg_match_all($this->patternUuid, $args->getNewValue($field), $newDocumentUuids);
        $newDocumentUuids = $this->prepareUuidsArray($newDocumentUuids);

        $newDocuments = [];
        if ($newDocumentUuids) {
            $entityManager = $args->getEntityManager();
            $newDocuments = $entityManager->getRepository(UserDocument::class)->findBy(['uuid' => $newDocumentUuids]);
            foreach ($newDocuments as $document) {
                $object->addDocument($document);
            }
        }

        $newDocuments = new ArrayCollection($newDocuments);
        $this->documentUuidsToRemove = $oldDocumentUuids->filter(function (UserDocument $document) use ($newDocuments) {
            return !$newDocuments->contains($document);
        });

        foreach ($this->documentUuidsToRemove as $document) {
            $object->removeDocument($document);
        }
    }

    private function prepareUuidsArray(array $uuids): array
    {
        $uuidObjects = [];

        foreach ($uuids[0] ?? [] as $uuid) {
            $uuidObjects[] = Uuid::fromString($uuid);
        }

        return $uuidObjects;
    }
}

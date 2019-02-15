<?php

namespace AppBundle\Entity\Listener;

use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Entity\IdeasWorkshop\Answer;
use AppBundle\Entity\UserDocument;
use AppBundle\Entity\UserDocumentInterface;
use AppBundle\UserDocument\UserDocumentManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainingUserDocumentListener
{
    private $documentManager;
    private $patternUuid;
    private $documentUuidsToRemove;

    public function __construct(ContainerInterface $container, string $patternUuid)
    {
        $this->documentManager = $container->get(UserDocumentManager::class);
        $this->patternUuid = '/'.$patternUuid.'/';
    }

    public function prePersistCommitteeFeed(CommitteeFeedItem $committeeFeedItem, LifecycleEventArgs $args): void
    {
        $this->prePersist($args, $committeeFeedItem->getContent(), $committeeFeedItem);
    }

    public function prePersistEvent(Event $event, LifecycleEventArgs $args): void
    {
        $this->prePersist($args, $event->getDescription(), $event);
    }

    public function prePersistAnswer(Answer $answer, LifecycleEventArgs $args): void
    {
        $this->prePersist($args, $answer->getContent(), $answer);
    }

    public function preUpdateCommitteeFeed(CommitteeFeedItem $committeeFeedItem, LifecycleEventArgs $args): void
    {
        $this->preUpdate($args, 'content', $committeeFeedItem);
    }

    public function preUpdateEvent(Event $event, PreUpdateEventArgs $args): void
    {
        $this->preUpdate($args, 'description', $event);
    }

    public function preUpdateAnswer(Answer $answer, PreUpdateEventArgs $args): void
    {
        $this->preUpdate($args, 'content', $answer);
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

    private function prePersist(LifecycleEventArgs $args, string $content, $object): void
    {
        if ($object instanceof UserDocumentInterface) {
            preg_match_all($this->patternUuid, $content, $documentUuids);
            $documentUuids = $this->prepareUuidsArray($documentUuids);

            $entityManager = $args->getEntityManager();
            if ($documents = $entityManager->getRepository(UserDocument::class)->findBy(['uuid' => $documentUuids])) {
                $object->setDocuments(new ArrayCollection($documents));
            }
        }
    }

    private function preUpdate(LifecycleEventArgs $args, string $field, $object): void
    {
        if ($object instanceof UserDocumentInterface) {
            if (!$args->hasChangedField($field)) {
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

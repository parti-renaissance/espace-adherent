<?php

namespace App\EventListener;

use App\Entity\IdeasWorkshop\Answer;
use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\Utils\EmojisRemover;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

/**
 * Removes emojis from object's text properties.
 */
class RemoveEmojisListener
{
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($this->getEntity($uow) as $object) {
            $class = \get_class($object);
            if (!$this->supportEntity($class)) {
                continue;
            }

            $changes = $uow->getEntityChangeSet($object);

            if (!$this->supportChanges($class, $changes)) {
                continue;
            }

            $this->removeEmojis($object);
            $uow->recomputeSingleEntityChangeSet($em->getClassMetadata($class), $object);
        }
    }

    private function removeEmojis($entity): void
    {
        switch (\get_class($entity)) {
            case Idea::class:
                $entity->setCanonicalName($this->getPurgedText($entity->getCanonicalName()));
                $entity->setName($this->getPurgedText($entity->getName()));
                if ($description = $entity->getDescription()) {
                    $entity->setDescription($this->getPurgedText($description));
                }
                break;
            case Answer::class:
            case Thread::class:
            case ThreadComment::class:
                $entity->setContent($this->getPurgedText($entity->getContent()));
                break;
        }
    }

    private function getPurgedText(string $text): string
    {
        return '' === $text ? $text : EmojisRemover::remove($text);
    }

    private function getEntity(UnitOfWork $getUnitOfWork): array
    {
        return array_merge(
            $getUnitOfWork->getScheduledEntityInsertions(),
            $getUnitOfWork->getScheduledEntityUpdates()
        );
    }

    private function supportEntity(string $class): bool
    {
        return \in_array($class, [
            Idea::class,
            Answer::class,
            ThreadComment::class,
            Thread::class,
        ], true);
    }

    private function supportChanges(string $get_class, array $change): bool
    {
        switch ($get_class) {
            case Idea::class:
                return !empty(array_intersect_key(array_keys($change), ['name', 'canonicalName', 'description']));
            case Answer::class:
            case Thread::class:
            case ThreadComment::class:
                return !empty(array_intersect_key(array_keys($change), ['content']));
        }

        return false;
    }
}

<?php

namespace App\Entity\Timeline;

use Doctrine\ORM\Event\PreUpdateEventArgs;

class MeasureTranslationListener
{
    public function preUpdate(MeasureTranslation $translation, PreUpdateEventArgs $event)
    {
        /** @var Measure $measure */
        $measure = $translation->getTranslatable();
        $measure->update();

        $em = $event->getEntityManager();
        $metadata = $em->getClassMetadata(Measure::class);

        $em->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $measure);
    }
}

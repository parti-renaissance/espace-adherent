<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Adherent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class RevokeManagedAreaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::SUBMIT => 'removeEmptyManagedArea',
        ];
    }

    public function removeEmptyManagedArea(FormEvent $event): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $event->getData()) {
            return;
        }

        $jecouteManagedArea = $adherent->getJecouteManagedArea();

        if ($jecouteManagedArea && null === $jecouteManagedArea->getZone()) {
            $adherent->revokeJecouteManager();
        }
    }
}

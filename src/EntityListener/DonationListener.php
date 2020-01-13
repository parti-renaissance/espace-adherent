<?php

namespace AppBundle\EntityListener;

use AppBundle\Donation\DonationEvents;
use AppBundle\Donation\DonatorWasUpdatedEvent;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DonationListener
{
    private $eventDispatcher;
    /** @var Donator */
    private $previousDonator;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function preUpdate(Donation $donation, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('donator')) {
            /** @var Donator */
            $this->previousDonator = $event->getOldValue('donator');
        }
    }

    public function postUpdate(Donation $donation): void
    {
        if ($this->previousDonator) {
            $this->eventDispatcher->dispatch(DonationEvents::DONATOR_UPDATED, new DonatorWasUpdatedEvent($this->previousDonator));
            $this->eventDispatcher->dispatch(DonationEvents::DONATOR_UPDATED, new DonatorWasUpdatedEvent($donation->getDonator()));
        }
    }
}

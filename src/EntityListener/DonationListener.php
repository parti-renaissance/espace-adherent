<?php

namespace App\EntityListener;

use App\Donation\DonationEvents;
use App\Donation\Event\DonatorWasUpdatedEvent;
use App\Entity\Donation;
use App\Entity\Donator;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DonationListener
{
    private $eventDispatcher;
    /** @var Donator */
    private $previousDonator;
    private $changeDonatedAt;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(Donation $donation): void
    {
        $this->dispatchDonatorUpdate($donation->getDonator());
    }

    public function postRemove(Donation $donation): void
    {
        $this->dispatchDonatorUpdate($donation->getDonator());
    }

    public function preUpdate(Donation $donation, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('donator')) {
            /** @var Donator */
            $this->previousDonator = $event->getOldValue('donator');
        }

        if ($event->hasChangedField('donatedAt')) {
            $this->changeDonatedAt = true;
        }
    }

    public function postUpdate(Donation $donation): void
    {
        if ($this->previousDonator) {
            $this->dispatchDonatorUpdate($this->previousDonator);
        }

        if ($this->changeDonatedAt || $this->previousDonator) {
            $this->dispatchDonatorUpdate($donation->getDonator());
        }
    }

    private function dispatchDonatorUpdate(Donator $donator): void
    {
        $this->eventDispatcher->dispatch(new DonatorWasUpdatedEvent($donator), DonationEvents::DONATOR_UPDATED);
    }
}

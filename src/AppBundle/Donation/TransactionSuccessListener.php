<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

class TransactionSuccessListener
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Update the database for the given donation with the Paybox data.
     *
     * @param PayboxResponseEvent $event
     */
    public function onPayboxIpnResponse(PayboxResponseEvent $event)
    {
        if (!$event->isVerified()) {
            return;
        }

        $payboxPayload = $event->getData();

        if (!isset($payboxPayload['id'], $payboxPayload['authorization'], $payboxPayload['result'])) {
            return;
        }

        $donation = $this->manager->getRepository(Donation::class)->findOneByUuid($payboxPayload['id']);

        if (!$donation) {
            return;
        }

        $donation->finish($payboxPayload);

        $this->manager->persist($donation);
        $this->manager->flush();
    }
}

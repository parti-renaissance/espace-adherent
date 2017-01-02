<?php

namespace AppBundle\Donation;

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

        $data = $event->getData();

        if (!isset($data['id'], $data['authorization'], $data['result'])) {
            return;
        }

        $donation = $this->manager->find('AppBundle:Donation', $data['id']);

        if (!$donation) {
            return;
        }

        $donation->setPayboxResultCode($data['result']);
        $donation->setPayboxAuthorizationCode($data['authorization']);
        $donation->setPayboxPayload($data);
        $donation->setDonatedAt(new \DateTime());
        $donation->setFinished(true);

        $this->manager->persist($donation);
        $this->manager->flush();
    }
}

<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\DonationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

class TransactionSuccessListener
{
    private $manager;
    private $mailjet;

    public function __construct(EntityManagerInterface $manager, MailjetService $mailjet)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
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

        if ($donation->isSuccessful()) {
            $this->mailjet->sendMessage(DonationMessage::createFromDonation($donation));
        }
    }
}

<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\DonationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;

class TransactionSuccessListener
{
    private $manager;
    private $mailjet;

    public function __construct(ObjectManager $manager, MailjetService $mailjet)
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

        $id = explode('_', $payboxPayload['id'])[0];
        $donation = $this->manager->getRepository(Donation::class)->findOneByUuid($id);

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

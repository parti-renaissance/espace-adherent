<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\DonationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransactionSubscriber implements EventSubscriberInterface
{
    private $manager;
    private $mailer;

    public function __construct(ObjectManager $manager, MailerService $mailer)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            PayboxEvents::PAYBOX_IPN_RESPONSE => ['onPayboxIpnResponse'],
        ];
    }

    /**
     * Update the database for the given donation with the Paybox data.
     */
    public function onPayboxIpnResponse(PayboxResponseEvent $event): void
    {
        if (!$event->isVerified()) {
            return;
        }

        $payboxPayload = $event->getData();

        if (!isset($payboxPayload['id'])) {
            return;
        }

        $id = explode('_', $payboxPayload['id'])[0];
        $donation = $this->manager->getRepository(Donation::class)->findOneByUuid($id);

        if (!$donation) {
            return;
        }

        $this->manager->persist($donation);
        $this->manager->persist($transaction = $donation->processPayload($payboxPayload));
        $this->manager->flush();

        if ($transaction->isSuccessful()) {
            $this->mailer->sendMessage(DonationMessage::createFromTransaction($transaction));
        }
    }
}

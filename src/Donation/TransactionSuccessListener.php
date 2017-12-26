<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\DonationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class TransactionSuccessListener
{
    private $manager;
    private $mailer;
    private $requestStack;

    public function __construct(ObjectManager $manager, MailerService $mailer, RequestStack $requestStack)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->requestStack = $requestStack;
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

        $campaignExpired = (bool) $this->requestStack->getCurrentRequest()->attributes->get('_campaign_expired', false);
        if (!$campaignExpired && $donation->isSuccessful()) {
            $this->mailer->sendMessage(DonationMessage::createFromDonation($donation));
        }
    }
}

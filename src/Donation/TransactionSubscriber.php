<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\DonationMessage;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransactionSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $manager;
    private $transactionRepository;
    private $donationRepository;
    private $logger;

    public function __construct(
        MailerService $mailer,
        ObjectManager $manager,
        TransactionRepository $transactionRepository,
        DonationRepository $donationRepository,
        LoggerInterface $logger
    ) {
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->transactionRepository = $transactionRepository;
        $this->donationRepository = $donationRepository;
        $this->logger = $logger;
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

        $payload = $event->getData();

        if (!$donation = $this->getDonation($payload)) {
            $this->logger->warning('[IPN] Donation not found', ['payload' => $payload]);

            return;
        }

        if ($this->transactionRepository->findByPayboxTransactionId($payload['transaction'])) {
            $this->logger->warning('[IPN] Transaction already exists', ['payload' => $payload]);

            return;
        }

        $transaction = $donation->processPayload($payload);

        if ($transaction->isSuccessful()) {
            $donation->markAsLastSuccessfulDonation();
        }

        $this->manager->persist($transaction);
        $this->manager->flush();

        if ($transaction->isSuccessful()) {
            $this->mailer->sendMessage(DonationMessage::createFromTransaction($transaction));
        }
    }

    private function getDonation(array $payload): ?Donation
    {
        $donationUuid = isset($payload['id']) ? explode('_', $payload['id'], 2)[0] : null;

        if (!$donationUuid || !Uuid::isValid($donationUuid)) {
            return null;
        }

        return $this->donationRepository->findOneByUuid($donationUuid);
    }
}

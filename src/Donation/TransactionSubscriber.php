<?php

namespace App\Donation;

use App\Entity\Donation;
use App\Mailer\MailerService;
use App\Mailer\Message\DonationThanksMessage;
use App\Membership\MembershipRequestHandler;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Lexik\Bundle\PayboxBundle\Event\PayboxEvents;
use Lexik\Bundle\PayboxBundle\Event\PayboxResponseEvent;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransactionSubscriber implements EventSubscriberInterface
{
    private MailerService $mailer;
    private ObjectManager $manager;
    private TransactionRepository $transactionRepository;
    private DonationRepository $donationRepository;
    private MembershipRequestHandler $membershipRequestHandler;
    private LoggerInterface $logger;

    public function __construct(
        MailerService $transactionalMailer,
        ObjectManager $manager,
        TransactionRepository $transactionRepository,
        DonationRepository $donationRepository,
        MembershipRequestHandler $membershipRequestHandler,
        LoggerInterface $logger
    ) {
        $this->mailer = $transactionalMailer;
        $this->manager = $manager;
        $this->transactionRepository = $transactionRepository;
        $this->donationRepository = $donationRepository;
        $this->membershipRequestHandler = $membershipRequestHandler;
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
            if ($donation->isMembership()) {
                $this->membershipRequestHandler->finishRenaissanceAdhesion($donation->getDonator()->getAdherent());

                return;
            }

            $this->mailer->sendMessage(DonationThanksMessage::createFromTransaction($transaction));
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

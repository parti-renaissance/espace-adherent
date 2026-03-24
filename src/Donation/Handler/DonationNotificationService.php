<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Adhesion\Events\NewCotisationEvent;
use App\Entity\Donation;
use App\Entity\Transaction;
use App\Mailer\MailerService;
use App\Mailer\Message\DonationThanksMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Service responsable des notifications suite à un paiement.
 */
class DonationNotificationService
{
    public function __construct(
        private readonly MailerService $mailer,
        private readonly MessageBusInterface $bus,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly SlackNotifier $slackNotifier,
    ) {
    }

    public function handleSuccessfulTransaction(Donation $donation, Transaction $transaction): void
    {
        $adherent = $donation->getDonator()?->getAdherent();

        // Rafraîchir les tags de l'adhérent
        if ($adherent) {
            $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));
        }

        // Gérer les cotisations membership
        if ($donation->isMembership()) {
            $this->handleMembershipCotisation($donation, $adherent);

            return;
        }

        // Envoyer le mail de remerciement pour les dons
        $this->sendThankYouEmail($donation, $transaction);
    }

    private function handleMembershipCotisation(Donation $donation, $adherent): void
    {
        if (!$adherent) {
            $this->logger->error('Adhesion RE: adherent introuvable pour une cotisation réussie, donation id '.$donation->getId());

            // On persiste quand même un flag pour le suivi
            $donation->setStatus('error_no_adherent');
            $this->entityManager->flush();

            return;
        }

        $this->eventDispatcher->dispatch(new NewCotisationEvent($adherent, $donation));
    }

    private function sendThankYouEmail(Donation $donation, Transaction $transaction): void
    {
        if (!$donation->hasSubscription() || $donation->isFirstSuccessfulTransaction($transaction)) {
            $this->mailer->sendMessage(DonationThanksMessage::createFromTransaction($transaction));

            // Log pour le suivi
            $this->logger->info('Thank you email sent', [
                'donation_id' => $donation->getId(),
                'transaction_id' => $transaction->getId(),
            ]);
        }
    }

    public function notifyAdminForHighValueDonation(Donation $donation): void
    {
        $this->logger->info('High value donation detected', [
            'donation_id' => $donation->getId(),
            'amount' => $donation->getAmount(),
        ]);

        // Notification Slack synchrone pour l'équipe finance
        $this->slackNotifier->notifyHighValueDonation($donation);
    }
}

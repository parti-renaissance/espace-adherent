<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Pronostic\Pronostic;
use App\JeMengage\Push\Command\PronosticNotificationCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\PronosticCreationMessage;
use App\Mailer\Message\Renaissance\PronosticResultMessage;
use App\Pronostic\PronosticReminderTypeEnum;
use App\Repository\AdherentRepository;
use App\Repository\Pronostic\PronosticParticipationRepository;
use App\Repository\Pronostic\PronosticRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:pronostic:dispatch-notifications',
    description: 'Dispatch pronostic notifications and emails (creation, J-1, H-1, results) for the displayed pronostic.',
)]
class DispatchPronosticNotificationsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly MessageBusInterface $bus,
        private readonly MailerService $transactionalMailer,
        private readonly PronosticRepository $pronosticRepository,
        private readonly PronosticParticipationRepository $participationRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pronostic = $this->pronosticRepository->findDisplayed();
        if (!$pronostic) {
            $this->io->note('Aucun pronostic affiché.');

            return self::SUCCESS;
        }

        $now = new \DateTimeImmutable();

        if (!$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::CREATION)) {
            $this->sendCreationEmails($pronostic);
            $this->markReminded($pronostic, PronosticReminderTypeEnum::CREATION);
            $this->io->success('Mail de création envoyé.');
        }

        if ($now < $pronostic->matchAt) {
            $oneDayBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 day');
            if ($now >= $oneDayBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::J_MINUS_1)) {
                $this->bus->dispatch(new PronosticNotificationCommand($pronostic->getUuid(), PronosticReminderTypeEnum::J_MINUS_1));
                $this->markReminded($pronostic, PronosticReminderTypeEnum::J_MINUS_1);
                $this->io->success('Push J-1 programmé.');
            }

            $oneHourBefore = \DateTimeImmutable::createFromInterface($pronostic->matchAt)->modify('-1 hour');
            if ($now >= $oneHourBefore && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::H_MINUS_1)) {
                $this->bus->dispatch(new PronosticNotificationCommand($pronostic->getUuid(), PronosticReminderTypeEnum::H_MINUS_1));
                $this->markReminded($pronostic, PronosticReminderTypeEnum::H_MINUS_1);
                $this->io->success('Push H-1 programmé.');
            }
        }

        if ($pronostic->isResultPublished() && !$pronostic->hasReminderBeenSent(PronosticReminderTypeEnum::RESULTS)) {
            $this->bus->dispatch(new PronosticNotificationCommand($pronostic->getUuid(), PronosticReminderTypeEnum::RESULTS));
            $this->sendResultEmails($pronostic);
            $this->markReminded($pronostic, PronosticReminderTypeEnum::RESULTS);
            $this->io->success('Push + mails de résultats envoyés.');
        }

        return self::SUCCESS;
    }

    private function sendCreationEmails(Pronostic $pronostic): void
    {
        $recipients = $this->adherentRepository
            ->createSubscribersQueryBuilder(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL)
            ->getQuery()
            ->getResult()
        ;

        foreach (array_chunk($recipients, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage(PronosticCreationMessage::create($pronostic, $chunk));
        }
    }

    private function sendResultEmails(Pronostic $pronostic): void
    {
        $participations = $this->participationRepository->findAllForPronostic($pronostic);

        foreach (array_chunk($participations, MailerService::PAYLOAD_MAXSIZE) as $chunk) {
            $this->transactionalMailer->sendMessage(PronosticResultMessage::create($pronostic, $chunk));
        }
    }

    private function markReminded(Pronostic $pronostic, PronosticReminderTypeEnum $type): void
    {
        $pronostic->markReminderSent($type);
        $this->entityManager->flush();
    }
}

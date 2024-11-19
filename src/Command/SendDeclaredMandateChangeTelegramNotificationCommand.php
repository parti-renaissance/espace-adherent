<?php

namespace App\Command;

use App\Entity\Reporting\DeclaredMandateHistory;
use App\Repository\Reporting\DeclaredMandateHistoryRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:declared-mandates:telegram-notify-changes',
    description: 'This command send notifications about changes in adherent declared mandates in a Telegram channel',
)]
class SendDeclaredMandateChangeTelegramNotificationCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly DeclaredMandateHistoryRepository $declaredMandateHistoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly ChatterInterface $chatter,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $telegramChatIdDeclaredMandates,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notNotifiedHistories = $this->declaredMandateHistoryRepository->findToNotifyOnTelegram();

        if (!$notNotifiedHistories) {
            $this->io->text('No new declared mandate history.');

            return self::SUCCESS;
        }

        $this->notifyTelegram($notNotifiedHistories);
        $this->markHistoriesAsTelegramNotified($notNotifiedHistories);

        $this->io->success('Notification sent!');

        return self::SUCCESS;
    }

    /** @param DeclaredMandateHistory[] $histories */
    private function notifyTelegram(array $histories): void
    {
        $this->io->text(\sprintf(
            'Will notify Telegram channel about %d new declared mandate historie(s)',
            \count($histories)
        ));

        foreach ($histories as $history) {
            $adherent = $history->getAdherent();

            $civility = match ($adherent->getGender()) {
                Genders::FEMALE => 'Mme',
                Genders::MALE => 'M.',
                default => '',
            };

            $added = $this->translateMandates($history->getAddedMandates());
            $removed = $this->translateMandates($history->getRemovedMandates());

            $messageBlock = [
                \sprintf(
                    '*%s %s* ([%s](%s))',
                    $civility,
                    $adherent->getFullName(),
                    $adherent->getId(),
                    $this->urlGenerator->generate('admin_app_adherent_edit', ['id' => $adherent->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                ),
            ];

            if (!empty($added)) {
                $messageBlock[] = \sprintf('Ajout : %s', implode(', ', $added));
            }

            if (!empty($removed)) {
                $messageBlock[] = \sprintf('Retrait : %s', implode(', ', $removed));
            }

            if ($administrator = $history->getAdministrator()) {
                $messageBlock[] = \sprintf(\PHP_EOL.'Par Admin : %s', $administrator->getEmailAddress());
            }

            $chatMessage = new ChatMessage(
                implode(\PHP_EOL, $messageBlock),
                (new TelegramOptions())
                    ->chatId($this->telegramChatIdDeclaredMandates)
                    ->parseMode(TelegramOptions::PARSE_MODE_MARKDOWN)
                    ->disableWebPagePreview(true)
                    ->disableNotification(true)
            );

            $this->chatter->send($chatMessage);
        }
    }

    /**
     * @param array|DeclaredMandateHistory[] $histories
     */
    private function markHistoriesAsTelegramNotified(array $histories): void
    {
        $this->io->text(\sprintf('Will mark %d new declared mandate histories as notified', \count($histories)));
        $this->io->progressStart(\count($histories));

        foreach ($histories as $declaredMandateHistory) {
            $declaredMandateHistory->setTelegramNotifiedAt(new \DateTimeImmutable());

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function translateMandates(array $mandates): array
    {
        return array_map(function (string $mandate): string {
            return $this->translator->trans("adherent.mandate.type.$mandate");
        }, $mandates);
    }
}

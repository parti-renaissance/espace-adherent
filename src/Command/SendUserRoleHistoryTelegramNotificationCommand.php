<?php

namespace App\Command;

use App\Entity\Reporting\UserRoleHistory;
use App\Repository\Reporting\UserRoleHistoryRepository;
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
    name: 'app:user-role:telegram-notify-changes',
    description: 'This command send notifications about changes in adherent roles in a Telegram channel',
)]
class SendUserRoleHistoryTelegramNotificationCommand extends Command
{
    private ?SymfonyStyle $io = null;

    public function __construct(
        private readonly UserRoleHistoryRepository $userRoleHistoryRepository,
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
        $notNotifiedHistories = $this->userRoleHistoryRepository->findToNotifyOnTelegram();

        if (!$notNotifiedHistories) {
            $this->io->text('No new declared mandate history.');

            return self::SUCCESS;
        }

        $this->notifyTelegram($notNotifiedHistories);
        $this->markHistoriesAsTelegramNotified($notNotifiedHistories);

        $this->io->success('Notifications sent!');

        return self::SUCCESS;
    }

    /** @param UserRoleHistory[] $histories */
    private function notifyTelegram(array $histories): void
    {
        $this->io->text(\sprintf(
            'Will notify Telegram channel about %d new role historie(s)',
            \count($histories)
        ));

        foreach ($histories as $history) {
            $user = $history->user;

            $civility = match ($user->getGender()) {
                Genders::FEMALE => 'Mme',
                Genders::MALE => 'M.',
                default => '',
            };

            $messageBlock = [
                \sprintf(
                    '*%s %s* ([%s](%s))',
                    $civility,
                    $user->getFullName(),
                    $user->getId(),
                    $this->urlGenerator->generate('admin_app_adherent_edit', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
                ),
            ];

            if (UserRoleHistory::ACTION_ADD === $history->action) {
                $messageBlock[] = \sprintf(
                    '❇️ %s (%s)',
                    $history->role,
                    implode(', ', $history->zones)
                );
            }

            if (UserRoleHistory::ACTION_REMOVE === $history->action) {
                $messageBlock[] = \sprintf(
                    '❌ %s (%s)',
                    $history->role,
                    implode(', ', $history->zones)
                );
            }

            if ($administrator = $history->adminAuthor) {
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
     * @param UserRoleHistory[] $histories
     */
    private function markHistoriesAsTelegramNotified(array $histories): void
    {
        $this->io->text(\sprintf('Will mark %d new role historie(s) as notified', \count($histories)));
        $this->io->progressStart(\count($histories));

        foreach ($histories as $userRoleHistory) {
            $userRoleHistory->telegramNotifiedAt = new \DateTimeImmutable();

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}

<?php

namespace App\Command;

use App\Entity\UserActionHistory;
use App\History\UserActionHistoryTypeEnum;
use App\Repository\UserActionHistoryRepository;
use App\Utils\StringCleaner;
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
        private readonly UserActionHistoryRepository $userActionHistoryRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly ChatterInterface $chatter,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $telegramChatIdNominations,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $notNotifiedHistories = $this->userActionHistoryRepository->findToNotifyOnTelegram([
            UserActionHistoryTypeEnum::ROLE_ADD,
            UserActionHistoryTypeEnum::ROLE_REMOVE,
        ]);

        if (!$notNotifiedHistories) {
            $this->io->text('No new role history.');

            return self::SUCCESS;
        }

        $this->io->text(\sprintf(
            'Will notify Telegram channel about %d new role historie(s)',
            $total = \count($notNotifiedHistories)
        ));

        $this->io->progressStart($total);

        foreach ($notNotifiedHistories as $history) {
            $this->process($history);

            $this->io->progressAdvance();
        }

        $this->io->progressFinish();

        $this->io->success('Notifications sent!');

        return self::SUCCESS;
    }

    private function process(UserActionHistory $history): void
    {
        $user = $history->adherent;

        $civility = match ($user->getGender()) {
            Genders::FEMALE => 'Mme',
            Genders::MALE => 'M.',
            default => '',
        };

        $messageBlock = [
            \sprintf(
                '*%s %s* \([%s](%s)\)',
                $civility,
                StringCleaner::escapeMarkdown($user->getFullName()),
                $user->getId(),
                $this->urlGenerator->generate('admin_app_adherent_edit', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ),
        ];

        if (UserActionHistoryTypeEnum::ROLE_ADD === $history->type) {
            $messageBlock[] = StringCleaner::escapeMarkdown(\sprintf(
                '❇️ %s (%s)',
                $this->translateRole($history->data['role']),
                implode(', ', $history->data['zones'])
            ));
        }

        if (UserActionHistoryTypeEnum::ROLE_REMOVE === $history->type) {
            $messageBlock[] = StringCleaner::escapeMarkdown(\sprintf(
                '❌ %s (%s)',
                $this->translateRole($history->data['role']),
                implode(', ', $history->data['zones'])
            ));
        }

        if ($administrator = $history->impersonator) {
            $messageBlock[] = \sprintf(\PHP_EOL.'Par Admin : %s', $administrator->getEmailAddress());
        }

        $chatMessage = new ChatMessage(
            implode(\PHP_EOL, $messageBlock),
            (new TelegramOptions())
                ->chatId($this->telegramChatIdNominations)
                ->parseMode(TelegramOptions::PARSE_MODE_MARKDOWN_V2)
                ->disableWebPagePreview(true)
                ->disableNotification(true)
        );

        $this->chatter->send($chatMessage);

        $this->markHistoryAsTelegramNotified($history);
    }

    private function markHistoryAsTelegramNotified(UserActionHistory $history): void
    {
        $history->telegramNotifiedAt = new \DateTimeImmutable();

        $this->entityManager->flush();
    }

    private function translateRole(string $role): string
    {
        $label = $this->translator->trans("role.$role", ['gender' => 'male']);
        if (str_starts_with($label, 'role.')) {
            return $role;
        }

        return $label;
    }
}

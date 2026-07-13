<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use App\Repository\MailchimpCampaignRepository;
use App\Ses\Campaign\Message\ReapStaleSendingRowsMessage;
use App\Ses\Campaign\Message\SendSesCampaignChunkMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Resends the recipients of a SES campaign whose send failed ambiguously and that no SES event ever confirmed.
 *
 * A send that fails on a 5xx or a network error leaves the row quarantined (SendErrored): SES may or may not
 * have accepted the mail, and AsyncAws gives us no way to tell (every transport failure, from a DNS error to a
 * read timeout, comes back as the same NetworkException). The row is therefore never resent automatically —
 * a wrong guess would mean sending the same mail twice.
 *
 * When no SES event at all lands on the row afterwards, the mail most likely never left, and those recipients
 * are simply missing from the campaign. This command hands that call to a human: it lists them and, on an
 * explicit confirmation, reopens them for a normal resend. The residual risk is stated up front — an event
 * lost on the webhook side would make this a double-send.
 */
#[AsCommand(
    name: 'ses:campaign:resend-quarantined',
    description: 'Resend the SES campaign recipients whose send failed and that no SES event ever confirmed',
)]
class ResendQuarantinedSesRowsCommand extends Command
{
    public function __construct(
        private readonly MailchimpCampaignRepository $campaignRepository,
        private readonly MailchimpStaticSegmentMemberRepository $memberRepository,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('campaign-id', InputArgument::REQUIRED, 'Id of the MailchimpCampaign to repair')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Skip the confirmation prompt')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $campaignId = (int) $input->getArgument('campaign-id');

        $campaign = $this->campaignRepository->find($campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            $io->error(\sprintf('Campaign %d not found.', $campaignId));

            return Command::FAILURE;
        }

        $segment = $campaign->getMailchimpStaticSegment();
        if (null === $segment) {
            $io->error(\sprintf('Campaign %d has no prepared segment.', $campaignId));

            return Command::FAILURE;
        }

        $rowIds = $this->memberRepository->findUnconfirmedQuarantinedRowIds($segment->id);
        if ([] === $rowIds) {
            $io->success('No unconfirmed quarantined recipient: nothing to resend.');

            return Command::SUCCESS;
        }

        $io->warning(\sprintf(
            "%d recipient(s) failed with an ambiguous send error and never got a single SES event.\n".
            'They most likely never received the mail — but if SES did send it and merely lost the event, '.
            'resending means they get it TWICE.',
            \count($rowIds),
        ));

        if (!$input->getOption('force') && !$io->confirm('Resend to them?', false)) {
            $io->comment('Aborted, nothing changed.');

            return Command::SUCCESS;
        }

        $reopened = 0;
        foreach ($rowIds as $rowId) {
            // Guarded on the absence of any SES event: a row proven sent since the listing above is left alone.
            if ($this->memberRepository->reopenQuarantinedRow($rowId)) {
                ++$reopened;
            }
        }

        if (0 === $reopened) {
            $io->success('Every listed row got confirmed in the meantime: nothing to resend.');

            return Command::SUCCESS;
        }

        // Back to Sending so the normal completion runs again on these rows (reach + stats), and re-arm the
        // watchdog that covers them. A campaign still Sending (send never completed) simply stays as it is.
        $this->campaignRepository->reopenForSending($campaignId);

        foreach ($this->memberRepository->findChunkNumbersToSend($segment->id) as $chunkNumber) {
            $this->bus->dispatch(new SendSesCampaignChunkMessage($campaignId, $chunkNumber));
        }

        $this->bus->dispatch(new ReapStaleSendingRowsMessage($campaignId));

        $io->success(\sprintf('%d recipient(s) reopened — the send is running again.', $reopened));

        return Command::SUCCESS;
    }
}

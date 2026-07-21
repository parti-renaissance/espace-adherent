<?php

declare(strict_types=1);

namespace App\Command;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Manual escape hatch: sends a campaign whose audience preparation refused to auto-send (blocked,
 * failed, or otherwise not `canSend()`), when a human has established the audience is actually usable.
 *
 * Bypasses canSend() — and only canSend(). Everything else on the real send path is kept: the recipient
 * guard, the Sent/Sending idempotence, and the retry pipeline all live in the downstream handlers, which
 * is why this dispatches the send command instead of reaching into the Manager.
 */
#[AsCommand(
    name: 'mailchimp:campaign:force-send',
    description: 'Force-send a campaign whose preparation blocked the auto-send, bypassing canSend().',
)]
class MailchimpCampaignForceSendCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('campaign-id', InputArgument::REQUIRED, 'MailchimpCampaign ID (the one shown in the [AudienceFinalize] Sentry context as campaign_id)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $campaignId = (int) $input->getArgument('campaign-id');

        $campaign = $this->entityManager->find(MailchimpCampaign::class, $campaignId);
        if (!$campaign instanceof MailchimpCampaign) {
            $io->error(\sprintf('MailchimpCampaign #%d not found.', $campaignId));

            return Command::FAILURE;
        }

        if (\in_array($campaign->status, [MailchimpStatusEnum::Sent, MailchimpStatusEnum::Sending], true)) {
            $io->warning(\sprintf('MailchimpCampaign #%d is already %s — nothing to force.', $campaignId, $campaign->status->value));

            return Command::SUCCESS;
        }

        $message = $campaign->getMessage();
        $channel = $campaign->sendViaMailchimp ? 'Mailchimp' : 'SES';

        $io->note([
            \sprintf('MailchimpCampaign  : #%d (status: %s, preparation: %s, block_reason: %s)',
                $campaign->getId(),
                $campaign->status->value,
                $campaign->getPreparationStatus()->value,
                $campaign->getBlockReason()?->value ?? 'null',
            ),
            \sprintf('AdherentMessage    : #%d (uuid: %s)', $message->getId(), $message->getUuid()->toRfc4122()),
            \sprintf('Send channel       : %s', $channel),
            'Bypassing canSend(). The recipient guard still applies, and a refusal still goes through the retry pipeline (11 attempts, 30s up to ~2h17).',
        ]);

        if (!$io->confirm('Proceed?', false)) {
            $io->warning('Aborted by user.');

            return Command::SUCCESS;
        }

        if ($campaign->sendViaMailchimp) {
            $this->bus->dispatch(new SendMailchimpCampaignCommand((int) $campaign->getId()));
        } else {
            $this->bus->dispatch(new TriggerSesCampaignMessage((int) $campaign->getId()));
        }

        $io->success(\sprintf(
            'Send dispatched for MailchimpCampaign #%d via %s. Watch Sentry for the retry-exhausted alert (raised only if every attempt fails).',
            $campaignId,
            $channel,
        ));

        return Command::SUCCESS;
    }
}

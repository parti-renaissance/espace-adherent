<?php

declare(strict_types=1);

namespace App\Command;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mailchimp:campaign:force-send',
    description: 'Force-send a MailchimpCampaign bypassing canSend(). The Mailchimp retry pipeline (30s → 60min, 6 attempts) stays active if Mailchimp refuses.',
)]
class MailchimpCampaignForceSendCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentMessageManager $manager,
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

        $message = $campaign->getMessage();
        if ($message->isSent()) {
            $io->warning(\sprintf('AdherentMessage #%d (uuid %s) already marked as sent. Aborting.', $message->getId(), $message->getUuid()->toString()));

            return Command::SUCCESS;
        }

        $io->note([
            \sprintf('MailchimpCampaign  : #%d (status: %s, block_reason: %s)',
                $campaign->getId(),
                $campaign->getStatus()->value,
                $campaign->getBlockReason()?->value ?? 'null',
            ),
            \sprintf('AdherentMessage    : #%d (uuid: %s)', $message->getId(), $message->getUuid()->toString()),
            'Bypassing canSend(). If Mailchimp refuses, RetrySendMailchimpCampaignCommand is dispatched (30s, then 30s/1m/5m/10m/30m/60m); only the final exhausted retry is reported to Sentry.',
        ]);

        if (!$io->confirm('Proceed?', false)) {
            $io->warning('Aborted by user.');

            return Command::SUCCESS;
        }

        $this->manager->send($message, $this->manager->getRecipients($message));

        $io->success(\sprintf(
            'Send pipeline triggered for MailchimpCampaign #%d. Watch Sentry for the [Mailchimp] Campaign retry exhausted alert (raised only if every retry fails).',
            $campaignId,
        ));

        return Command::SUCCESS;
    }
}

<?php

namespace App\AdherentMessage\MailchimpCampaign\Handler;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use Psr\Log\LoggerInterface;

class GenericMailchimpCampaignHandler implements MailchimpCampaignHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public static function getPriority(): int
    {
        return -255;
    }

    public function handle(AdherentMessageInterface $message): void
    {
        $this->logger->error(sprintf('Message %d has %d Mailchimp campaigns', $message->getId(), \count($message->getMailchimpCampaigns())));

        if (!\count($message->getMailchimpCampaigns())) {
            $message->setMailchimpCampaigns([new MailchimpCampaign($message)]);
        }
    }

    public function supports(AdherentMessageInterface $message): bool
    {
        return true;
    }
}

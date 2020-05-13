<?php

namespace App\Legislative;

use App\Mailer\MailerService;
use App\Mailer\Message\LegislativeCampaignContactMessage as MailerLegislativeCampaignContactMessage;

class LegislativeCampaignContactMessageHandler
{
    private $mailer;
    private $financialHotlineEmailAddress;
    private $standardHotlineEmailAddress;

    public function __construct(
        MailerService $mailer,
        string $financialHotlineEmailAddress,
        string $standardHotlineEmailAddress
    ) {
        $this->mailer = $mailer;
        $this->financialHotlineEmailAddress = $financialHotlineEmailAddress;
        $this->standardHotlineEmailAddress = $standardHotlineEmailAddress;
    }

    public function handle(LegislativeCampaignContactMessage $message): void
    {
        $this->mailer->sendMessage(MailerLegislativeCampaignContactMessage::createFromCampaignContactMessage(
            $message,
            $message->isAddressedToFinancialHotline() ? $this->financialHotlineEmailAddress : $this->standardHotlineEmailAddress
        ));
    }
}

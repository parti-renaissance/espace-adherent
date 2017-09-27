<?php

namespace AppBundle\Legislative;

use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\LegislativeCampaignContactMessage as MailjetLegislativeCampaignContactMessage;

class LegislativeCampaignContactMessageHandler
{
    private $mailer;
    private $financialHotlineEmailAddress;
    private $standardHotlineEmailAddress;

    public function __construct(MailerService $mailer, string $financialHotlineEmailAddress, string $standardHotlineEmailAddress)
    {
        $this->mailer = $mailer;
        $this->financialHotlineEmailAddress = $financialHotlineEmailAddress;
        $this->standardHotlineEmailAddress = $standardHotlineEmailAddress;
    }

    public function handle(LegislativeCampaignContactMessage $message): void
    {
        $this->mailer->sendMessage(MailjetLegislativeCampaignContactMessage::createFromCampaignContactMessage(
            $message,
            $message->isAddressedToFinancialHotline() ? $this->financialHotlineEmailAddress : $this->standardHotlineEmailAddress
        ));
    }
}

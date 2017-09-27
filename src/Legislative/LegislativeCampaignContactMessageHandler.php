<?php

namespace AppBundle\Legislative;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\LegislativeCampaignContactMessage as MailjetLegislativeCampaignContactMessage;

class LegislativeCampaignContactMessageHandler
{
    private $mailjet;
    private $financialHotlineEmailAddress;
    private $standardHotlineEmailAddress;

    public function __construct(MailjetService $mailjet, string $financialHotlineEmailAddress, string $standardHotlineEmailAddress)
    {
        $this->mailjet = $mailjet;
        $this->financialHotlineEmailAddress = $financialHotlineEmailAddress;
        $this->standardHotlineEmailAddress = $standardHotlineEmailAddress;
    }

    public function handle(LegislativeCampaignContactMessage $message): void
    {
        $this->mailjet->sendMessage(MailjetLegislativeCampaignContactMessage::createFromCampaignContactMessage(
            $message,
            $message->isAddressedToFinancialHotline() ? $this->financialHotlineEmailAddress : $this->standardHotlineEmailAddress
        ));
    }
}

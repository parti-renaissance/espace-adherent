<?php

namespace AppBundle\Legislative;

use AppBundle\Mail\Transactional\LegislativeCampaignContactMail;
use EnMarche\MailerBundle\MailPost\MailPostInterface;

class LegislativeCampaignContactMessageHandler
{
    private $mailPost;
    private $financialHotlineEmailAddress;
    private $standardHotlineEmailAddress;

    public function __construct(MailPostInterface $mailPost, string $financialHotlineEmailAddress, string $standardHotlineEmailAddress)
    {
        $this->mailPost = $mailPost;
        $this->financialHotlineEmailAddress = $financialHotlineEmailAddress;
        $this->standardHotlineEmailAddress = $standardHotlineEmailAddress;
    }

    public function handle(LegislativeCampaignContactMessage $message): void
    {
        $this->mailPost->address(
            LegislativeCampaignContactMail::class,
            LegislativeCampaignContactMail::createRecipient(
                $message->isAddressedToFinancialHotline()
                    ? $this->financialHotlineEmailAddress
                    : $this->standardHotlineEmailAddress
            ),
            null,
            LegislativeCampaignContactMail::createTemplateVars($message),
            LegislativeCampaignContactMail::SUBJECT,
            LegislativeCampaignContactMail::createSender($message),
            LegislativeCampaignContactMail::createCcRecipients($message)
        );
    }
}

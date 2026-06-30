<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use PHPUnit\Framework\TestCase;

/**
 * getMailchimpId() (used for the admin/normalizer Mailchimp preview link) now keys off the campaign's
 * external id alone — the dead `synchronized` flag gate has been removed.
 */
class AdherentMessageGetMailchimpIdTest extends TestCase
{
    public function testReturnsNullWhenNoCampaignHasAnExternalId(): void
    {
        $message = new AdherentMessage();
        $message->addMailchimpCampaign(new MailchimpCampaign($message));

        self::assertNull($message->getMailchimpId());
    }

    public function testReturnsTheExternalIdOfTheMailchimpSentCampaign(): void
    {
        $message = new AdherentMessage();
        $campaign = new MailchimpCampaign($message);
        $campaign->setExternalId('mc-campaign-123');
        $message->addMailchimpCampaign($campaign);

        self::assertSame('mc-campaign-123', $message->getMailchimpId());
    }
}

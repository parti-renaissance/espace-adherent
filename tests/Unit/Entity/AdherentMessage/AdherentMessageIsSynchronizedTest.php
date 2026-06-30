<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use PHPUnit\Framework\TestCase;

/**
 * isSynchronized() is a pure completeness check since the async sync barrier was removed: it must be
 * true as soon as the message is complete (campaign + content + subject), with no deferred flag.
 */
class AdherentMessageIsSynchronizedTest extends TestCase
{
    public function testStatutoryMessageIsAlwaysSynchronized(): void
    {
        $message = new AdherentMessage();
        $message->setIsStatutory(true);

        self::assertTrue($message->isSynchronized());
    }

    public function testMessageWithoutCampaignIsNotSynchronized(): void
    {
        $message = new AdherentMessage();
        $message->setSubject('Sujet');
        $message->setContent('<p>Contenu</p>');

        self::assertFalse($message->isSynchronized());
    }

    public function testCompleteMessageWithCampaignIsSynchronizedImmediately(): void
    {
        $message = new AdherentMessage();
        $message->setSubject('Sujet');
        $message->setContent('<p>Contenu</p>');
        $message->addMailchimpCampaign(new MailchimpCampaign($message));

        self::assertTrue($message->isSynchronized());
    }

    public function testMessageMissingSubjectIsNotSynchronized(): void
    {
        $message = new AdherentMessage();
        $message->setContent('<p>Contenu</p>');
        $message->addMailchimpCampaign(new MailchimpCampaign($message));

        self::assertFalse($message->isSynchronized());
    }
}

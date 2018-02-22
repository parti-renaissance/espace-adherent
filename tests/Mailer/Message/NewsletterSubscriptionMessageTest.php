<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailer\Message\NewsletterSubscriptionMessage;

/**
 * @group message
 */
class NewsletterSubscriptionMessageTest extends MessageTestCase
{
    /**
     * @var NewsletterSubscription|null
     */
    private $newsletterSubscription;

    public function testCreate(): void
    {
        $message = NewsletterSubscriptionMessage::create($this->newsletterSubscription);

        self::assertMessage(NewsletterSubscriptionMessage::class, [], $message);

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient('recipient@example.com', null, [], $message);

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->newsletterSubscription = $this->createMock(NewsletterSubscription::class);

        $this->newsletterSubscription
            ->expects(self::once())
            ->method('getEmail')
            ->willReturn('recipient@example.com')
        ;
    }

    protected function tearDown()
    {
        $this->newsletterSubscription = null;
    }
}

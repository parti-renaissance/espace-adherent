<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailer\Message\CommitteeMessageNotificationMessage;

/**
 * @group message
 */
class CommitteeMessageNotificationMessageTest extends MessageTestCase
{
    /**
     * @var CommitteeFeedItem|null
     */
    private $committeeFeedItem;

    public function testCreate(): void
    {
        $message = CommitteeMessageNotificationMessage::create(
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->committeeFeedItem,
            'Sujet de message test'
        );

        self::assertMessage(
            CommitteeMessageNotificationMessage::class,
            [
                'host_first_name' => 'Animateur',
                'subject' => 'Sujet de message test',
                'message' => 'Contenu de message test.',
            ],
            $message
        );

        self::assertSender('Animateur Jones', null, $message);
        self::assertReplyTo('host@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'host_first_name' => 'Animateur',
                'subject' => 'Sujet de message test',
                'message' => 'Contenu de message test.',
                'first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'host_first_name' => 'Animateur',
                'subject' => 'Sujet de message test',
                'message' => 'Contenu de message test.',
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->committeeFeedItem = $this->createMock(CommitteeFeedItem::class);

        $this->committeeFeedItem
            ->expects(self::once())
            ->method('getAuthor')
            ->willReturn($this->createAdherent('host@example.com', 'Animateur', 'Jones'))
        ;
        $this->committeeFeedItem
            ->expects(self::once())
            ->method('getContent')
            ->willReturn('Contenu de message test.')
        ;
    }

    protected function tearDown()
    {
        $this->committeeFeedItem = null;
    }
}

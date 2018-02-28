<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\BoardMember\BoardMemberMessage as BoardMemberMessageModel;
use AppBundle\Mailer\Message\BoardMemberMessage;

/**
 * @group message
 */
class BoardMemberMessageTest extends MessageTestCase
{
    /**
     * @var BoardMemberMessageModel|null
     */
    private $boardMemberMessage;

    public function testCreateFromModel(): void
    {
        $message = BoardMemberMessage::create(
            $this->boardMemberMessage,
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ]
        );

        self::assertMessage(
            BoardMemberMessage::class,
            [
                'member_first_name' => 'Référent',
                'member_last_name' => 'Jones',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Référent Jones', 'jemarche@en-marche.fr', $message);
        self::assertReplyTo('referent@example.com', $message);

        self::assertCountRecipients(3, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'member_first_name' => 'Référent',
                'member_last_name' => 'Jones',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'member_first_name' => 'Référent',
                'member_last_name' => 'Jones',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );
        self::assertMessageRecipient(
            'jemarche@en-marche.fr',
            'Je Marche',
            [
                'member_first_name' => 'Référent',
                'member_last_name' => 'Jones',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->boardMemberMessage = $this->createMock(BoardMemberMessageModel::class);

        $this->boardMemberMessage
            ->expects(self::once())
            ->method('getFrom')
            ->willReturn($this->createAdherent('referent@example.com', 'Référent', 'Jones'))
        ;
        $this->boardMemberMessage
            ->expects(self::once())
            ->method('getSubject')
            ->willReturn('Sujet de test')
        ;
        $this->boardMemberMessage
            ->expects(self::once())
            ->method('getContent')
            ->willReturn('Contenu du message de test.')
        ;
    }

    protected function tearDown()
    {
        $this->boardMemberMessage = null;
    }
}

<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Mailer\Message\CitizenProjectCommentMessage;

/**
 * @group message
 */
class CitizenProjectCommentMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProjectComment|null
     */
    private $citizenProjectComment;

    public function testCreate(): void
    {
        $message = CitizenProjectCommentMessage::create(
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->citizenProjectComment
        );

        self::assertMessage(
            CitizenProjectCommentMessage::class,
            [
                'citizen_project_host_first_name' => 'Auteur',
                'citizen_project_host_message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Auteur Jones', null, $message);
        self::assertReplyTo('author@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'citizen_project_host_first_name' => 'Auteur',
                'citizen_project_host_message' => 'Contenu du message de test.',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'citizen_project_host_first_name' => 'Auteur',
                'citizen_project_host_message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->citizenProjectComment = $this->createMock(CitizenProjectComment::class);

        $this->citizenProjectComment
            ->expects(self::once())
            ->method('getAuthor')
            ->willReturn($this->createAdherent('author@example.com', 'Auteur', 'Jones'))
        ;
        $this->citizenProjectComment
            ->expects(self::once())
            ->method('getContent')
            ->willReturn('Contenu du message de test.')
        ;
    }

    protected function tearDown()
    {
        $this->citizenProjectComment = null;
    }
}

<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Mailer\Message\ReferentMessage;
use AppBundle\Referent\ReferentMessage as ReferentMessageModel;

/**
 * @group message
 */
class ReferentMessageTest extends MessageTestCase
{
    /**
     * @var ReferentMessageModel|null
     */
    private $referentMessage;

    public function testCreateFromModel(): void
    {
        $message = ReferentMessage::create(
            $this->referentMessage,
            [
                $this->createReferentManagedUser('jean@example.com', 'Jean', 'Doe'),
                $this->createReferentManagedUser('bernard@example.com', 'Bernard', 'Smith'),
            ]
        );

        self::assertMessage(
            ReferentMessage::class,
            [
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Votre référent En Marche !', null, $message);
        self::assertReplyTo('referent@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'message' => 'Contenu du message de test.',
                'recipient_first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'message' => 'Contenu du message de test.',
                'recipient_first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->referentMessage = $this->createMock(ReferentMessageModel::class);

        $this->referentMessage
            ->method('getFrom')
            ->willReturn($this->createAdherent('referent@example.com', 'Référent', 'Jones'))
        ;
        $this->referentMessage
            ->method('getContent')
            ->willReturn('Contenu du message de test.')
        ;
    }

    protected function tearDown()
    {
        $this->referentMessage = null;
    }

    private function createReferentManagedUser(string $email, string $firstName, string $lastName): ReferentManagedUser
    {
        $referentManagedUser = $this->createMock(ReferentManagedUser::class);

        $referentManagedUser
            ->method('getEmail')
            ->willReturn($email)
        ;
        $referentManagedUser
            ->method('getFullName')
            ->willReturn("$firstName $lastName")
        ;
        $referentManagedUser
            ->method('getFirstName')
            ->willReturn($firstName)
        ;

        return $referentManagedUser;
    }
}

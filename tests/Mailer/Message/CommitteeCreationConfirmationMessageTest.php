<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Committee;
use AppBundle\Mailer\Message\CommitteeCreationConfirmationMessage;

/**
 * @group message
 */
class CommitteeCreationConfirmationMessageTest extends MessageTestCase
{
    /**
     * @var Committee|null
     */
    private $committee;

    public function testCreate(): void
    {
        $message = CommitteeCreationConfirmationMessage::create(
            $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            $this->committee
        );

        self::assertMessage(
            CommitteeCreationConfirmationMessage::class,
            [
                'first_name' => 'Bernard',
                'committee_city' => 'Lille',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'first_name' => 'Bernard',
                'committee_city' => 'Lille',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->committee = $this->createMock(Committee::class);

        $this->committee
            ->expects(self::once())
            ->method('getCityName')
            ->willReturn('Lille')
        ;
    }

    protected function tearDown()
    {
        $this->committee = null;
    }
}

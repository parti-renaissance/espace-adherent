<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Committee;
use AppBundle\Mailer\Message\CommitteeApprovalConfirmationMessage;

/**
 * @group message
 */
class CommitteeApprovalConfirmationMessageTest extends MessageTestCase
{
    /**
     * @var Committee|null
     */
    private $committee;

    public function testCreate(): void
    {
        $message = CommitteeApprovalConfirmationMessage::create(
            $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            $this->committee,
            'https://enmarche.code/comites/foo-bar'
        );

        self::assertMessage(
            CommitteeApprovalConfirmationMessage::class,
            [
                'first_name' => 'Bernard',
                'committee_city' => 'Lille',
                'committee_url' => 'https://enmarche.code/comites/foo-bar',
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
                'committee_url' => 'https://enmarche.code/comites/foo-bar',
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

<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Committee;
use AppBundle\Mailer\Message\CommitteeApprovalReferentMessage;

/**
 * @group message
 */
class CommitteeApprovalReferentMessageTest extends MessageTestCase
{
    /**
     * @var Committee|null
     */
    private $committee;

    public function testCreate(): void
    {
        $message = CommitteeApprovalReferentMessage::create(
            $this->createAdherent('referent@example.com', 'Référent', 'Jones'),
            $this->createAdherent('creator@example.com', 'Créateur', 'Doe'),
            $this->committee,
            'https://enmarche.code/contact/foo-bar'
        );

        self::assertMessage(
            CommitteeApprovalReferentMessage::class,
            [
                'first_name' => 'Référent',
                'committee_name' => 'Comité #1',
                'committee_city' => 'Lille',
                'creator_first_name' => 'Créateur',
                'creator_contact_link' => 'https://enmarche.code/contact/foo-bar',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'referent@example.com',
            'Référent Jones',
            [
                'first_name' => 'Référent',
                'committee_name' => 'Comité #1',
                'committee_city' => 'Lille',
                'creator_first_name' => 'Créateur',
                'creator_contact_link' => 'https://enmarche.code/contact/foo-bar',
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
            ->method('getName')
            ->willReturn('Comité #1')
        ;
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

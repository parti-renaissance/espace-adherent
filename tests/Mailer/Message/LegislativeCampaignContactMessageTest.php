<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Legislative\LegislativeCampaignContactMessage as CampaignContactMessage;
use AppBundle\Mailer\Message\LegislativeCampaignContactMessage;

/**
 * @group message
 */
class LegislativeCampaignContactMessageTest extends MessageTestCase
{
    /**
     * @var CampaignContactMessage|null
     */
    private $campaignContactMessage;

    public function testCreate(): void
    {
        $message = LegislativeCampaignContactMessage::create(
            $this->campaignContactMessage,
            'recipient@example.com'
        );

        self::assertMessage(
            LegislativeCampaignContactMessage::class,
            [
                'email' => 'jean@example.com',
                'first_name' => 'Jean',
                'last_name' => 'Doe',
                'department_number' => '06',
                'electoral_district_number' => '89',
                'role' => 'Candidat',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Jean Doe', null, $message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'recipient@example.com',
            null,
            [
                'email' => 'jean@example.com',
                'first_name' => 'Jean',
                'last_name' => 'Doe',
                'department_number' => '06',
                'electoral_district_number' => '89',
                'role' => 'Candidat',
                'subject' => 'Sujet de test',
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertCountCC(1, $message);
        self::assertMessageCC('jean@example.com', $message);
    }

    protected function setUp()
    {
        // Can not mock this ValueObject since it is final.
        $this->campaignContactMessage = new CampaignContactMessage();

        $this->campaignContactMessage->setEmailAddress('jean@example.com');
        $this->campaignContactMessage->setFirstName('Jean');
        $this->campaignContactMessage->setLastName('Doe');
        $this->campaignContactMessage->setDepartmentNumber('06');
        $this->campaignContactMessage->setElectoralDistrictNumber('89');
        $this->campaignContactMessage->setRole('Candidat');
        $this->campaignContactMessage->setSubject('Sujet de test');
        $this->campaignContactMessage->setMessage('Contenu du message de test.');
    }

    protected function tearDown()
    {
        $this->campaignContactMessage = null;
    }
}

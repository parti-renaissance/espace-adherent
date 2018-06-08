<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenActionNotificationMessage;

/**
 * @group message
 */
class CitizenActionNotificationMessageTest extends MessageTestCase
{
    /**
     * @var CitizenAction|null
     */
    private $citizenAction;

    public function testCreate(): void
    {
        $message = CitizenActionNotificationMessage::create(
            [
                $this->createAdherent('recipient1@example.com', 'recipient1', 'Martin'),
                $this->createAdherent('recipient2@example.com', 'Bernard', 'Henry'),
            ],
            $this->createAdherent('host@example.com', 'Host', 'Jones'),
            $this->citizenAction,
            'https://enmarche.code/attend-url?test=test&toto=tata'
        );

        self::assertMessage(
            CitizenActionNotificationMessage::class,
            [
                'host_first_name' => 'Host',
                'citizen_project_name' => 'CP-Name',
                'citizen_action_name' => 'Action Citoyenne #1',
                'citizen_action_date' => 'dimanche 14 janvier 2018',
                'citizen_action_hour' => '02h02',
                'citizen_action_address' => '1, rue des champs - 75001 Paris',
                'citizen_action_attend_url' => 'https://enmarche.code/attend-url?test=test&toto=tata',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('host@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'recipient1@example.com',
            'recipient1 Martin',
            [
                'host_first_name' => 'Host',
                'citizen_project_name' => 'CP-Name',
                'citizen_action_name' => 'Action Citoyenne #1',
                'citizen_action_date' => 'dimanche 14 janvier 2018',
                'citizen_action_hour' => '02h02',
                'citizen_action_address' => '1, rue des champs - 75001 Paris',
                'citizen_action_attend_url' => 'https://enmarche.code/attend-url?test=test&toto=tata',
                'recipient_first_name' => 'recipient1',
            ],
            $message
        );
        self::assertMessageRecipient(
            'recipient2@example.com',
            'Bernard Henry',
            [
                'host_first_name' => 'Host',
                'citizen_project_name' => 'CP-Name',
                'citizen_action_name' => 'Action Citoyenne #1',
                'citizen_action_date' => 'dimanche 14 janvier 2018',
                'citizen_action_hour' => '02h02',
                'citizen_action_address' => '1, rue des champs - 75001 Paris',
                'citizen_action_attend_url' => 'https://enmarche.code/attend-url?test=test&toto=tata',
                'recipient_first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $citizenProject = $this->createMock(CitizenProject::class);
        $citizenProject->method('getName')->willReturn('CP-Name');

        $this->citizenAction = $this->createMock(CitizenAction::class);
        $this->citizenAction->method('getName')->willReturn('Action Citoyenne #1');
        $this->citizenAction->method('getCitizenProject')->willReturn($citizenProject);
        $this->citizenAction->method('getBeginAt')->willReturn(new \DateTime('2018-01-14 02:02:29'));
        $this->citizenAction->method('getInlineFormattedAddress')->willReturn('1, rue des champs - 75001 Paris');
    }

    protected function tearDown()
    {
        $this->citizenAction = null;
    }
}

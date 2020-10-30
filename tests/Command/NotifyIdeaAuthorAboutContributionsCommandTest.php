<?php

namespace Tests\App\Command;

use App\Mailer\Message\IdeaNotificationWithoutContributionsMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group command
 */
class NotifyIdeaAuthorAboutContributionsCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testSendMailAboutContributions(): void
    {
        $this->runCommand('idea-workshop:notification:contributions');

        $this->assertCountMails(1, IdeaNotificationWithoutContributionsMessage::class, 'michel.vasseur@example.ch');
    }

    protected function setUp(): void
    {
        $this->init();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }
}

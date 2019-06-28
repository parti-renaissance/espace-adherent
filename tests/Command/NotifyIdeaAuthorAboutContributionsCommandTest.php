<?php

namespace Tests\AppBundle\Command;

use AppBundle\Mailer\Message\IdeaContributionsMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group command
 */
class NotifyIdeaAuthorAboutContributionsCommandTest extends WebTestCase
{
    use ControllerTestTrait;

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

    public function testSendMailAboutContributions(): void
    {
        $this->runCommand('idea-workshop:notification:contributions');

        $this->assertCountMails(1, IdeaContributionsMessage::class, 'michel.vasseur@example.ch');
    }
}

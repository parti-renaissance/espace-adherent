<?php

namespace Tests\AppBundle\Command;

use AppBundle\DataFixtures\ORM\LoadIdeaData;
use AppBundle\Mailer\Message\IdeaFinalizeMessage;
use Cake\Chronos\Chronos;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group command
 */
class NotifyIdeaAuthorCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testSendMailWhenNoteIsFinished(): void
    {
        Chronos::setTestNow('-13 days -10 minutes');

        $this->loadFixtures([
            LoadIdeaData::class,
        ]);

        $this->runCommand('idea-workshop:notification:idea-author');

        $this->assertCountMails(1, IdeaFinalizeMessage::class, 'jacques.picard@en-marche.fr');
    }

    public function testSendMailWhenNoteWillBeFinishedIn3Days(): void
    {
        Chronos::setTestNow('-10 days -10 minutes');

        $this->loadFixtures([
            LoadIdeaData::class,
        ]);

        $this->runCommand('idea-workshop:notification:idea-author', ['--caution' => null]);

        $this->assertCountMails(1, IdeaFinalizeMessage::class, 'jacques.picard@en-marche.fr');
    }

    public function setUp()
    {
        $this->container = $this->getContainer();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        Chronos::setTestNow();

        parent::tearDown();
    }
}

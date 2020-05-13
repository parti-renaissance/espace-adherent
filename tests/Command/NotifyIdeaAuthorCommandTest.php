<?php

namespace Tests\App\Command;

use App\DataFixtures\ORM\LoadIdeaData;
use App\Entity\IdeasWorkshop\Idea;
use App\Mailer\Message\IdeaFinalizeNotificationMessage;
use App\Mailer\Message\IdeaFinalizePreNotificationMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group command
 */
class NotifyIdeaAuthorCommandTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testSendMailWhenNoteIsFinished(): void
    {
        $this->updateIdeaFinalizedAt(LoadIdeaData::IDEA_04_UUID, '-10 minutes');

        $this->runCommand('idea-workshop:notification:idea-author');

        $this->assertCountMails(1, IdeaFinalizeNotificationMessage::class, 'jacques.picard@en-marche.fr');
    }

    public function testSendMailWhenNoteWillBeFinishedIn3Days(): void
    {
        $this->updateIdeaFinalizedAt(LoadIdeaData::IDEA_04_UUID, '+3 days -10 minutes');

        $this->runCommand('idea-workshop:notification:idea-author', ['--caution' => null]);

        $this->assertCountMails(1, IdeaFinalizePreNotificationMessage::class, 'jacques.picard@en-marche.fr');
    }

    public function setUp()
    {
        $this->init();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }

    private function updateIdeaFinalizedAt(string $uuid, string $time): void
    {
        $this
            ->manager
            ->createQueryBuilder()
            ->update(Idea::class, 'i')
            ->set('i.finalizedAt', ':finalizedAt')
            ->where('i.uuid = :uuid')
            ->setParameter('finalizedAt', new \DateTime($time), 'datetime')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->execute()
        ;
    }
}

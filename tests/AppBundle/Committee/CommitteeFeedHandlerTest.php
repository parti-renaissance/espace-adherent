<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeFeedHandler;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeFeedMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeFeedHandlerTest extends WebTestCase
{
    use TestHelperTrait;

    private $committeeFeedMessageRepository;

    public function testCreateMessage()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_5_UUID);
        $committee = $this->getCommitteeRepository()->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);

        $messageContent = 'Bienvenue !';

        $committeeFeedHandler = new CommitteeFeedHandler($this->getManagerRegistry()->getManager());
        $committeeMessage = new CommitteeMessage();
        $committeeMessage->content = $messageContent;

        $message = $committeeFeedHandler->createMessage($committeeMessage, $committee, $adherent);

        $this->assertInstanceOf(CommitteeFeedMessage::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $messageByAuthor = $this->committeeFeedMessageRepository->findOneByAuthor($adherent);

        $this->assertSame($message, $messageByAuthor);

        $messageByCommittee = $this->committeeFeedMessageRepository->findOneByCommittee($committee);

        $this->assertSame($message, $messageByCommittee);
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->committeeFeedMessageRepository = $this->getCommitteeFeedMessageRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->committeeFeedMessageRepository = null;

        parent::tearDown();
    }
}

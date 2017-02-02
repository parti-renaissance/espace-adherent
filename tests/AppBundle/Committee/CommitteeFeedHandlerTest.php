<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeFeedHandler;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeFeedHandlerTest extends WebTestCase
{
    use TestHelperTrait;

    /* @var CommitteeFeedHandler */
    private $handler;

    /* @var CommitteeFeedItemRepository */
    private $committeeFeedItemRepository;

    /* @var AdherentRepository */
    private $adherentRepository;

    /* @var CommitteeRepository */
    private $committeeRepository;

    public function testCreateMessage()
    {
        $adherent = $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_5_UUID);
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);

        $messageContent = 'Bienvenue !';

        $committeeMessage = new CommitteeMessage();
        $committeeMessage->content = $messageContent;

        $message = $this->handler->createMessage($committeeMessage, $committee, $adherent);

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $messageByAuthor = $this->committeeFeedItemRepository->findOneByAuthor($adherent);

        $this->assertSame($message, $messageByAuthor);

        $messageByCommittee = $this->committeeFeedItemRepository->findOneByCommittee($committee);

        $this->assertSame($message, $messageByCommittee);
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->adherentRepository = $this->getAdherentRepository();
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeFeedItemRepository = $this->getCommitteeFeedItemRepository();
        $this->handler = new CommitteeFeedHandler($this->getManagerRegistry()->getManager());

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->handler = null;
        $this->adherentRepository = null;
        $this->committeeRepository = null;
        $this->committeeFeedItemRepository = null;

        parent::tearDown();
    }
}

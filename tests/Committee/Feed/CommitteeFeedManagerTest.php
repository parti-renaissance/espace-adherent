<?php

namespace Tests\App\Committee\Feed;

use App\Committee\Feed\CommitteeFeedManager;
use App\Committee\Feed\CommitteeMessage;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\CommitteeFeedItem;
use App\Mailer\Message\CommitteeMessageNotificationMessage;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\Repository\EmailRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group committee
 */
class CommitteeFeedManagerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeFeedManager */
    private $committeeFeedManager;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;

    public function testCreateMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $author = $this->committeeMembershipRepository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());
    }

    public function testCreateNoNotificationMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $author = $this->committeeMembershipRepository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent, true, 'now', false));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());
    }

    public function setUp()
    {
        $this->init();

        $this->committeeFeedManager = $this->get(CommitteeFeedManager::class);
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        $this->committeeFeedManager = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;

        parent::tearDown();
    }
}

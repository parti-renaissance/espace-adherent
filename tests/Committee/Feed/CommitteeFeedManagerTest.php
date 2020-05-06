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

    /* @var EmailRepository */
    private $emailRepository;

    public function testCreateMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $author = $this->committeeMembershipRepository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $this->assertCountMails(1, CommitteeMessageNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, CommitteeMessageNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(1, CommitteeMessageNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(0, CommitteeMessageNotificationMessage::class, 'carl999@example.fr');
    }

    public function testCreateNoNotificationMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $author = $this->committeeMembershipRepository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent, true, 'now', false));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $this->assertCountMails(0, CommitteeMessageNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(0, CommitteeMessageNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(0, CommitteeMessageNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(0, CommitteeMessageNotificationMessage::class, 'carl999@example.fr');
    }

    public function setUp()
    {
        $this->init();

        $this->committeeFeedManager = $this->get('app.committee.feed_manager');
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
        $this->emailRepository = $this->getEmailRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->kill();

        $this->committeeFeedManager = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->emailRepository = null;

        parent::tearDown();
    }
}

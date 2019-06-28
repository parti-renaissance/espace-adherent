<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailer\Message\CommitteeMessageNotificationMessage;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EmailRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

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

    protected function setUp(): void
    {
        $this->init();

        $this->committeeFeedManager = $this->get('app.committee.feed_manager');
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
        $this->emailRepository = $this->getEmailRepository();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->committeeFeedManager = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->emailRepository = null;

        parent::tearDown();
    }

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
}

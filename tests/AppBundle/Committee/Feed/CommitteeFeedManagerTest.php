<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailjet\Message\CommitteeMessageNotificationMessage;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\MailjetEmailRepository;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeFeedManagerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    /* @var CommitteeFeedManager */
    private $manager;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;

    /* @var MailjetEmailRepository */
    private $mailjetEmailRepository;

    public function testCreateMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $author = $this->committeeMembershipRepository->findHostMembers(LoadAdherentData::COMMITTEE_1_UUID)->first();

        $messageContent = 'Bienvenue !';

        $message = $this->manager->createMessage(new CommitteeMessage($author, $committee, $messageContent));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $this->assertCountMails(1, CommitteeMessageNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, CommitteeMessageNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(1, CommitteeMessageNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(0, CommitteeMessageNotificationMessage::class, 'carl999@example.fr');
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->manager = $this->get('app.committee.feed_manager');
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
        $this->mailjetEmailRepository = $this->getMailjetEmailRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->manager = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->mailjetEmailRepository = null;

        parent::tearDown();
    }
}

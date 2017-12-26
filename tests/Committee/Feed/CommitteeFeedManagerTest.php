<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mailer\Message\CommitteeMessageNotificationMessage;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EmailRepository;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class CommitteeFeedManagerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    /* @var CommitteeFeedManager */
    private $manager;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;

    /* @var EmailRepository */
    private $emailRepository;

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

    public function testAnonymizeOrganizerCitizenInitiatives()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

        $author = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $this->assertCount(44, $this->getCommitteeFeedItemRepository()->findAll());
        $this->assertCount(39, $this->getCommitteeFeedItemRepository()->findBy(['author' => $author]));
        $this->manager->removeAuthorItems($author);
        $this->assertCount(5, $this->getCommitteeFeedItemRepository()->findAll());
        $this->assertCount(0, $this->getCommitteeFeedItemRepository()->findBy(['author' => $author]));
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
        $this->emailRepository = $this->getEmailRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->manager = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->emailRepository = null;

        parent::tearDown();
    }
}

<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeFeedHandler;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\MailjetEmailRepository;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeFeedHandlerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    /* @var CommitteeFeedHandler */
    private $handler;

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

        $subscribersCount = $this->committeeMembershipRepository->findFollowers(LoadAdherentData::COMMITTEE_1_UUID)
            ->getCommitteesNotificationsSubscribers()
            ->count();

        $messageContent = 'Bienvenue !';

        $committeeMessage = new CommitteeMessage();
        $committeeMessage->content = $messageContent;

        $message = $this->handler->createMessage($committeeMessage, $committee, $author);

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $this->assertCount($subscribersCount, $this->getMailjetEmailRepository()->findAll());
    }

    public function setUp()
    {
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
        $this->handler = $this->get('app.committee.committee_feed_handler');
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
        $this->mailjetEmailRepository = $this->getMailjetEmailRepository();

        parent::setUp();
    }

    public function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->handler = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->mailjetEmailRepository = null;

        parent::tearDown();
    }
}

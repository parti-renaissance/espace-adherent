<?php

namespace Tests\AppBundle\Committee\Feed;

use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Mail\Campaign\CommitteeMessageNotificationMail;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EmailRepository;
use EnMarche\MailerBundle\Test\MailTestCaseTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group committee
 */
class CommitteeFeedManagerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MailTestCaseTrait;

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

        $this->assertMailSentForRecipients(['jacques.picard@en-marche.fr', 'luciole1989@spambox.fr', 'gisele-berthoux@caramail.com'], CommitteeMessageNotificationMail::class);
    }

    public function testCreateNoNotificationMessage()
    {
        $committee = $this->committeeRepository->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID);
        $author = $this->committeeMembershipRepository->findHostMembers($this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID))->first();

        $messageContent = 'Bienvenue !';
        $message = $this->committeeFeedManager->createMessage(new CommitteeMessage($author, $committee, 'Foo subject', $messageContent, true, 'now', false));

        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame($messageContent, $message->getContent());

        $this->assertMailCountForClass(0, CommitteeMessageNotificationMail::class);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
        ]);

        $this->committeeFeedManager = $this->get(CommitteeFeedManager::class);
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown()
    {
        $this->clearMails();

        $this->kill();

        $this->committeeFeedManager = null;
        $this->committeeRepository = null;
        $this->committeeMembershipRepository = null;
        $this->emailRepository = null;

        parent::tearDown();
    }
}

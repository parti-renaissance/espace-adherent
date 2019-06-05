<?php

namespace Tests\AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use AppBundle\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use AppBundle\AdherentMessage\MailchimpCampaign\Handler\ReferentMailchimpCampaignHandler;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use AppBundle\Entity\Committee;
use AppBundle\Entity\District;
use AppBundle\Entity\ReferentTag;
use AppBundle\Mailchimp\Campaign\CampaignContentRequestBuilder;
use AppBundle\Mailchimp\Campaign\CampaignRequestBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\CommitteeMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\DeputyMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\ReferentMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\Listener\SetCampaignReplyToSubscriber;
use AppBundle\Mailchimp\Campaign\Listener\UpdateCampaignSubjectSubscriber;
use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Campaign\SegmentConditionsBuilder;
use AppBundle\Mailchimp\Driver;
use AppBundle\Mailchimp\Manager;
use AppBundle\Repository\AdherentMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentMessageChangeCommandHandlerTest extends TestCase
{
    private $adherentDummy;
    private $commandDummy;
    private $clientMock;

    public function testCommitteeMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(CommitteeAdherentMessage::class);
        $message->setFilter($committeeFilter = new CommitteeFilter());
        $committeeFilter->setCommittee($this->createConfiguredMock(Committee::class, [
            'getName' => 'Committee name',
            'getMailchimpId' => 456,
        ]));

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '3',
                        'template_id' => 3,
                        'subject_line' => '[Comité] Subject',
                        'title' => 'Full Name - '.date('d/m/Y'),
                        'reply_to' => 'jemarche@en-marche.fr',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 456,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/123/content', ['json' => [
                    'template' => [
                        'id' => 3,
                        'sections' => [
                            'content' => 'Content',
                            'committee_link' => '<a target="_blank" href="https://committee_url" title="Voir le comité" style="text-align:center;font-size:16px;color:#000;font-family:roboto,helvetica neue,helvetica,arial,sans-serif">Committee name</a>',
                            'reply_to_link' => '<a class="mcnButton" title="RÉPONDRE" href="mailto:adherent@mail.com" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#2BBAFF;">RÉPONDRE</a>',
                        ],
                    ],
                ]]]
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 123])))
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testReferentMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ReferentAdherentMessage::class);

        $message->setFilter($filter = new ReferentUserFilter([
            $tag1 = new ReferentTag('Tag1', 'code1'),
            $tag2 = new ReferentTag('Tag2', 'code2'),
        ]));
        $tag1->setExternalId(123);
        $tag2->setExternalId(456);

        (new ReferentMailchimpCampaignHandler())->handle($message);

        $this->clientMock
            ->expects($this->exactly(4))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '1',
                        'template_id' => 1,
                        'subject_line' => '[Référent] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - code1',
                        'reply_to' => 'jemarche@en-marche.fr',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [1],
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontains',
                                    'field' => 'interests-A',
                                    'value' => [2, 3, 4, 5, 6],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/campaign_id1/content', ['json' => [
                    'template' => [
                        'id' => 1,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                            'reply_to_link' => '<a class="mcnButton" title="RÉPONDRE" href="mailto:adherent@mail.com" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#FF6955;mso-line-height-rule:exactly;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;">RÉPONDRE</a>',
                        ],
                    ],
                ]]],
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '1',
                        'template_id' => 1,
                        'subject_line' => '[Référent] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - code2',
                        'reply_to' => 'jemarche@en-marche.fr',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [1],
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontains',
                                    'field' => 'interests-A',
                                    'value' => [2, 3, 4, 5, 6],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 456,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/campaign_id2/content', ['json' => [
                    'template' => [
                        'id' => 1,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                            'reply_to_link' => '<a class="mcnButton" title="RÉPONDRE" href="mailto:adherent@mail.com" target="_blank" style="font-weight:normal;letter-spacing:normal;line-height:100%;text-align:center;text-decoration:none;color:#FF6955;mso-line-height-rule:exactly;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;display:block;">RÉPONDRE</a>',
                        ],
                    ],
                ]]]
            )
            ->willReturn(
                new Response(200, [], json_encode(['id' => 'campaign_id1'])),
                new Response(200, [], json_encode(['id' => 'campaign_id1'])),
                new Response(200, [], json_encode(['id' => 'campaign_id2'])),
                new Response(200, [], json_encode(['id' => 'campaign_id2']))
            )
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testDeputyMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(DeputyAdherentMessage::class);
        $message->setFilter($filter = new AdherentZoneFilter($tag = new ReferentTag('Tag1', 'code1')));
        $tag->setExternalId(123);

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '2',
                        'template_id' => 2,
                        'subject_line' => '[Député] Subject',
                        'title' => 'Full Name - '.date('d/m/Y'),
                        'reply_to' => 'ne-pas-repondre@en-marche.fr',
                        'from_name' => 'Full Name',
                    ],
                    'recipients' => [
                        'list_id' => 'listId',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontainsall',
                                    'field' => 'interests-C',
                                    'value' => [],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/123/content', ['json' => [
                    'template' => [
                        'id' => 2,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                            'full_name' => 'Full Name',
                            'district_name' => 'District1',
                        ],
                    ],
                ]]]
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 123])))
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    protected function setUp()
    {
        $this->adherentDummy = $this->createConfiguredMock(Adherent::class, [
            '__toString' => 'Full Name',
            'getFullName' => 'Full Name',
            'getFirstName' => 'First Name',
            'getEmailAddress' => 'adherent@mail.com',
            'getManagedDistrict' => $this->createConfiguredMock(District::class, ['__toString' => 'District1']),
        ]);

        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->commandDummy = $this->createMock(AdherentMessageChangeCommand::class);
        $this->commandDummy->expects($this->once())->method('getUuid')->willReturn(Uuid::uuid4());
    }

    private function preparedMessage(string $messageClass): AdherentMessageInterface
    {
        /** @var AdherentMessageInterface $message */
        $message = new $messageClass(Uuid::uuid4(), $this->adherentDummy);
        $message->setSubject('Subject');
        $message->setContent('Content');
        $message->addMailchimpCampaign(new MailchimpCampaign($message));

        return $message;
    }

    private function creatRequestBuildersLocator(): ContainerInterface
    {
        return new SimpleContainer([
            CampaignRequestBuilder::class => new CampaignRequestBuilder(
                $mailchimpMapping = new MailchimpObjectIdMapping(
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'citizen_project' => 4,
                    ],
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'citizen_project' => 4,
                    ],
                    [
                        'subscribed_emails_referents' => 1,
                        'CITIZEN_PROJECT_HOST' => 2,
                        'COMMITTEE_SUPERVISOR' => 3,
                        'COMMITTEE_HOST' => 4,
                        'COMMITTEE_FOLLOWER' => 5,
                        'COMMITTEE_NO_FOLLOWER' => 6,
                    ],
                    'A',
                    'B',
                    'C'
                ),
                new SegmentConditionsBuilder($mailchimpMapping),
                'listId',
                'FromName'
            ),
            CampaignContentRequestBuilder::class => new CampaignContentRequestBuilder($mailchimpMapping, $this->createSectionRequestBuildersLocator()),
        ]);
    }

    private function createSectionRequestBuildersLocator(): ContainerInterface
    {
        return new SimpleContainer([
            CommitteeAdherentMessage::class => new CommitteeMessageSectionBuilder($this->createConfiguredMock(UrlGeneratorInterface::class, ['generate' => 'https://committee_url'])),
            ReferentAdherentMessage::class => new ReferentMessageSectionBuilder(),
            DeputyAdherentMessage::class => new DeputyMessageSectionBuilder(),
        ]);
    }

    private function createRepositoryMock(AdherentMessageInterface $message): AdherentMessageRepository
    {
        $repositoryMock = $this->createMock(AdherentMessageRepository::class);
        $repositoryMock->expects($this->once())->method('findOneByUuid')->willReturn($message);

        return $repositoryMock;
    }

    private function createHandler(AdherentMessageInterface $message): AdherentMessageChangeCommandHandler
    {
        return new AdherentMessageChangeCommandHandler(
            $this->createRepositoryMock($message),
            new Manager(
                new Driver($this->clientMock, 'test'),
                $this->creatRequestBuildersLocator(),
                $this->createEventDispatcher()
            ),
            $this->createMock(ObjectManager::class)
        );
    }

    private function createEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new SetCampaignReplyToSubscriber());
        $eventDispatcher->addSubscriber(new UpdateCampaignSubjectSubscriber());

        return $eventDispatcher;
    }
}

class SimpleContainer implements ContainerInterface
{
    private $container;

    public function __construct(array $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        return $this->container[$id] ?? null;
    }

    public function has($id)
    {
        return isset($this->container[$id]);
    }
}

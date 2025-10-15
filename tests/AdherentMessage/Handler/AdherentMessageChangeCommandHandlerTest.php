<?php

namespace Tests\App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\GenericMailchimpCampaignHandler;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Mailchimp\Campaign\CampaignContentRequestBuilder;
use App\Mailchimp\Campaign\CampaignRequestBuilder;
use App\Mailchimp\Campaign\ContentSection\BasicMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\CommitteeMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\DeputyMessageSectionBuilder;
use App\Mailchimp\Campaign\Listener\UpdateCampaignSubjectSubscriber;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentGeoZoneConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentInterestConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentRegistrationDateConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentSegmentConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\CommitteeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactAgeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactCityConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactNameConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\JecouteConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToAdherentConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\SubscriptionTypeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ToElectedRepresentativeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionsBuilder;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tests\App\AbstractKernelTestCase;

class AdherentMessageChangeCommandHandlerTest extends AbstractKernelTestCase
{
    private $adherentDummy;
    private $commandDummy;
    private $clientMock;
    /** @var MailchimpObjectIdMapping */
    private $mailchimpMapping;

    public function testCommitteeMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ScopeEnum::ANIMATOR);
        $message->setFilter($committeeFilter = new MessageFilter());
        $committeeFilter->setCommittee($this->createConfiguredMock(Committee::class, [
            'getName' => 'Committee name',
            'getMailchimpId' => 456,
        ]));

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '11',
                    'template_id' => 11,
                    'subject_line' => '[Comité] Subject',
                    'title' => date('Y/m/d').' - Full Name : Subject',
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'main_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'StaticSegment',
                                'op' => 'static_is',
                                'field' => 'static_segment',
                                'value' => 456,
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [],
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/123/content', ['json' => [
                'template' => [
                    'id' => 11,
                    'sections' => [
                        'content' => 'Content',
                        'committee_link' => '<a target="_blank" href="https://committee_url" title="Voir le comité">Committee name</a>',
                        'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                        'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                    ],
                ],
            ]]],
        ];

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);

                return $this->createMockResponse(json_encode(['id' => 123]));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testCandidateMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ScopeEnum::CANDIDATE);
        $message->setFilter(new AdherentGeoZoneFilter(new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '2',
                    'template_id' => 2,
                    'subject_line' => '[Candidat] Subject',
                    'title' => date('Y/m/d').' - Full Name : Subject',
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'main_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'TextMerge',
                                'op' => 'contains',
                                'field' => 'ZONE_DPT',
                                'value' => ' (code1)',
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [],
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/campaign_id1/content', ['json' => [
                'template' => [
                    'id' => 2,
                    'sections' => [
                        'content' => 'Content',
                        'full_name' => 'Full Name',
                        'first_name' => 'First Name',
                        'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                        'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                    ],
                ],
            ]]],
        ];

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);

                return $this->createMockResponse(json_encode(['id' => 'campaign_id1']));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testCandidateJecouteMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ScopeEnum::CANDIDATE);
        $message->setFilter(new JecouteFilter(new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '2',
                    'template_id' => 2,
                    'subject_line' => '[Candidat] Subject',
                    'title' => date('Y/m/d').' - Full Name : Subject',
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'main_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'TextMerge',
                                'op' => 'is',
                                'field' => 'CODE_DPT',
                                'value' => 'code1',
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [],
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/campaign_id1/content', ['json' => [
                'template' => [
                    'id' => 2,
                    'sections' => [
                        'content' => 'Content',
                        'full_name' => 'Full Name',
                        'first_name' => 'First Name',
                        'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                        'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                    ],
                ],
            ]]],
        ];

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);

                return $this->createMockResponse(json_encode(['id' => 'campaign_id1']));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testCorrespondentMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ScopeEnum::CORRESPONDENT);
        $message->setFilter(new AdherentGeoZoneFilter(new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '7',
                    'template_id' => 7,
                    'subject_line' => '[Responsable local] Subject',
                    'title' => date('Y/m/d').' - Full Name : Subject',
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'main_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'TextMerge',
                                'op' => 'contains',
                                'field' => 'ZONE_DPT',
                                'value' => ' (code1)',
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [1],
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/id1/content', ['json' => [
                'template' => [
                    'id' => 7,
                    'sections' => [
                        'content' => 'Content',
                    ],
                ],
            ]]],
        ];

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);

                return $this->createMockResponse(json_encode(['id' => 'id1']));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testRegionalCoordinatorMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ScopeEnum::REGIONAL_COORDINATOR);
        $message->setFilter(new MessageFilter([new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')]));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '9',
                    'template_id' => 9,
                    'subject_line' => '[Coordinateur Régional] Subject',
                    'title' => date('Y/m/d').' - Full Name : Subject',
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'main_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'TextMerge',
                                'op' => 'contains',
                                'field' => 'ZONE_DPT',
                                'value' => ' (code1)',
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [1],
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/id1/content', ['json' => [
                'template' => [
                    'id' => 9,
                    'sections' => [
                        'content' => 'Content',
                    ],
                ],
            ]]],
        ];

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);

                return $this->createMockResponse(json_encode(['id' => 'id1']));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentDummy = $this->createConfiguredMock(Adherent::class, [
            '__toString' => 'Full Name',
            'getFullName' => 'Full Name',
            'getFirstName' => 'First Name',
            'getEmailAddress' => 'adherent@mail.com',
            'getDeputyZone' => $this->createConfiguredMock(Zone::class, ['__toString' => 'District1']),
        ]);

        $this->clientMock = $this->createMock(HttpClientInterface::class);
        $this->commandDummy = $this->createMock(AdherentMessageChangeCommand::class);
        $this->commandDummy->expects($this->once())->method('getUuid')->willReturn(Uuid::uuid4());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->adherentDummy = null;
        $this->clientMock = null;
        $this->commandDummy = null;
    }

    private function preparedMessage(string $instanceScope): AdherentMessageInterface
    {
        $message = new AdherentMessage(Uuid::uuid4(), $this->adherentDummy);
        $message->setSender($this->adherentDummy);
        $message->setSubject('Subject');
        $message->setContent('Content');
        $message->setInstanceScope($instanceScope);
        $message->addMailchimpCampaign(new MailchimpCampaign($message));

        return $message;
    }

    private function creatRequestBuildersLocator(): ContainerInterface
    {
        return new SimpleContainer([
            CampaignRequestBuilder::class => new CampaignRequestBuilder(
                $this->mailchimpMapping = new MailchimpObjectIdMapping(
                    'main_list_id',
                    'newsletter_list_id',
                    'elected_representative_list_id',
                    'event_inscription_list_id',
                    'jecoute_list_id',
                    'jemengage_list_id',
                    'newsletter_legislative_candidate_list_id',
                    'newsletter_renaissance_list_id',
                    array_flip(ScopeEnum::ALL),
                    array_flip(ScopeEnum::ALL),
                    [
                        'subscribed_emails_referents' => 1,
                        'COMMITTEE_SUPERVISOR' => 3,
                        'COMMITTEE_HOST' => 4,
                        'COMMITTEE_FOLLOWER' => 5,
                        'COMMITTEE_NO_FOLLOWER' => 6,
                        'deputy_email' => 7,
                        'senator_email' => 8,
                    ],
                    'A',
                    'B',
                    'C',
                    'https://mailchimp.com',
                    'xyz'
                ),
                new SegmentConditionsBuilder($this->mailchimpMapping, [
                    new AdherentGeoZoneConditionBuilder(),
                    new AdherentInterestConditionBuilder($this->mailchimpMapping),
                    new AdherentRegistrationDateConditionBuilder(),
                    new AdherentSegmentConditionBuilder($this->mailchimpMapping),
                    new CommitteeConditionBuilder($this->mailchimpMapping),
                    new ContactNameConditionBuilder(),
                    new ContactAgeConditionBuilder(),
                    new ContactCityConditionBuilder(),
                    new JecouteConditionBuilder(),
                    new ReferentToAdherentConditionBuilder($this->mailchimpMapping),
                    new SubscriptionTypeConditionBuilder($this->mailchimpMapping),
                    new ToElectedRepresentativeConditionBuilder($this->mailchimpMapping),
                ])
            ),
            CampaignContentRequestBuilder::class => new CampaignContentRequestBuilder(
                $this->mailchimpMapping,
                $this->createSectionRequestBuildersIterable()
            ),
        ]);
    }

    private function createSectionRequestBuildersIterable(): iterable
    {
        return [
            new CommitteeMessageSectionBuilder($this->createConfiguredMock(UrlGeneratorInterface::class, ['generate' => 'https://committee_url'])),
            new BasicMessageSectionBuilder(),
            new DeputyMessageSectionBuilder(),
        ];
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
                new Driver($this->clientMock, 'test_main'),
                $this->creatRequestBuildersLocator(),
                $this->createEventDispatcher(),
                $this->mailchimpMapping
            ),
            $this->createMock(ObjectManager::class)
        );
    }

    private function createEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new UpdateCampaignSubjectSubscriber());

        return $eventDispatcher;
    }

    private function createMockResponse(string $content, int $statusCode = 200): ResponseInterface
    {
        return $this->createConfiguredMock(ResponseInterface::class, [
            'getContent' => $content,
            'getStatusCode' => $statusCode,
            'toArray' => json_decode($content, true),
        ]);
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

    public function has($id): bool
    {
        return isset($this->container[$id]);
    }
}

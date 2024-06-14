<?php

namespace Tests\App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\AdherentZoneMailchimpCampaignHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\GenericMailchimpCampaignHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\ReferentMailchimpCampaignHandler;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CandidateAdherentMessage;
use App\Entity\AdherentMessage\CandidateJecouteMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\CorrespondentAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\RegionalCoordinatorAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;
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
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentZoneConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\CommitteeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactAgeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactCityConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactNameConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\JecouteConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToAdherentConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToCandidateConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\SubscriptionTypeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ToElectedRepresentativeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionsBuilder;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
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
        $message = $this->preparedMessage(CommitteeAdherentMessage::class);
        $message->setFilter($committeeFilter = new MessageFilter());
        $committeeFilter->setCommittee($this->createConfiguredMock(Committee::class, [
            'getName' => 'Committee name',
            'getMailchimpId' => 456,
        ]));

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '3',
                    'template_id' => 3,
                    'subject_line' => '[Comité] Subject',
                    'title' => 'Full Name - '.date('d/m/Y'),
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
                    'id' => 3,
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

    public function testReferentMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(ReferentAdherentMessage::class);

        $message->setFilter(new ReferentUserFilter([
            $tag1 = new ReferentTag('Tag1', 'code1', new Zone('mock', 'code1', 'Tag1')),
            $tag2 = new ReferentTag('Tag2', 'code2', new Zone('mock', 'code1', 'Tag2')),
        ]));
        $tag1->setExternalId(123);
        $tag2->setExternalId(456);

        (new ReferentMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '1',
                    'template_id' => 1,
                    'subject_line' => '[Référent] Subject',
                    'title' => 'Full Name - '.date('d/m/Y').' - code1',
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
                                'value' => 123,
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
            ['PUT', '/3.0/campaigns/campaign_id1/content', ['json' => [
                'template' => [
                    'id' => 1,
                    'sections' => [
                        'content' => 'Content',
                        'full_name' => 'Full Name',
                        'first_name' => 'First Name',
                        'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                        'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
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
                                'value' => [1],
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
                        'full_name' => 'Full Name',
                        'first_name' => 'First Name',
                        'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                        'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                    ],
                ],
            ]]],
        ];

        $this->clientMock
            ->expects($this->exactly(4))
            ->method('request')
            ->willReturnCallback(function (...$args) use (&$series) {
                $expectedArgs = array_shift($series);
                $this->assertSame($expectedArgs, $args);

                return $this->createMockResponse(json_encode(['id' => 'campaign_id'.(\count($series) > 1 ? '1' : '2')]));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testDeputyMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(DeputyAdherentMessage::class);
        $message->setFilter(new AdherentZoneFilter($tag = new ReferentTag('Tag1', 'code1', new Zone('mock', 'code1', 'Tag1'))));
        $tag->setExternalId(123);

        (new AdherentZoneMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '2',
                    'template_id' => 2,
                    'subject_line' => '[Délégué de circonscription] Subject',
                    'title' => 'Full Name - '.date('d/m/Y').' - code1',
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
                                'value' => 123,
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [7],
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
                        'full_name' => 'Full Name',
                        'first_name' => 'First Name',
                        'district_name' => 'District1',
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

    public function testSenatorMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(SenatorAdherentMessage::class);
        $filter = new AdherentZoneFilter($tag = new ReferentTag('Tag1', 'code1', new Zone('mock', 'code1', 'Tag1'))); // 5 and 6 are included by default
        $filter->setIncludeCommitteeSupervisors(false); // exclude 3
        $filter->setIncludeCommitteeHosts(true); // include 4

        $message->setFilter($filter);
        $tag->setExternalId(123);

        (new AdherentZoneMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'folder_id' => '6',
                    'template_id' => 6,
                    'subject_line' => '[Sénateur] Subject',
                    'title' => 'Full Name - '.date('d/m/Y').' - code1',
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'main_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontains',
                                'field' => 'interests-A',
                                'value' => [4],
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestnotcontains',
                                'field' => 'interests-A',
                                'value' => [3],
                            ],
                            [
                                'condition_type' => 'StaticSegment',
                                'op' => 'static_is',
                                'field' => 'static_segment',
                                'value' => 123,
                            ],
                            [
                                'condition_type' => 'Interests',
                                'op' => 'interestcontainsall',
                                'field' => 'interests-C',
                                'value' => [8],
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/123/content', ['json' => [
                'template' => [
                    'id' => 6,
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

                return $this->createMockResponse(json_encode(['id' => 123]));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testCandidateMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(CandidateAdherentMessage::class);
        $message->setFilter(new AdherentGeoZoneFilter(new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'template_id' => 7,
                    'subject_line' => '[Candidat] Subject',
                    'title' => 'Full Name - '.date('d/m/Y'),
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
                    'id' => 7,
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
        $message = $this->preparedMessage(CandidateJecouteMessage::class);
        $message->setFilter(new JecouteFilter(new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'template_id' => 8,
                    'subject_line' => 'Subject',
                    'title' => 'Full Name - '.date('d/m/Y'),
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'Full Name | Renaissance',
                ],
                'recipients' => [
                    'list_id' => 'jecoute_list_id',
                    'segment_opts' => [
                        'match' => 'all',
                        'conditions' => [
                            [
                                'condition_type' => 'TextMerge',
                                'op' => 'is',
                                'field' => 'CODE_DPT',
                                'value' => 'code1',
                            ],
                        ],
                    ],
                ],
            ]]],
            ['PUT', '/3.0/campaigns/campaign_id1/content', ['json' => [
                'template' => [
                    'id' => 8,
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

                return $this->createMockResponse(json_encode(['id' => 'campaign_id1']));
            })
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testCorrespondentMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(CorrespondentAdherentMessage::class);
        $message->setFilter(new AdherentGeoZoneFilter(new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'template_id' => 10,
                    'subject_line' => '[Responsable local] Subject',
                    'title' => 'Full Name - '.date('d/m/Y'),
                    'reply_to' => 'contact@parti-renaissance.fr',
                    'from_name' => 'First Name | Campagne 2022',
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
                    'id' => 10,
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
        $message = $this->preparedMessage(RegionalCoordinatorAdherentMessage::class);
        $message->setFilter(new MessageFilter([new Zone(Zone::DEPARTMENT, 'code1', 'Tag1')]));

        (new GenericMailchimpCampaignHandler())->handle($message);

        $series = [
            ['POST', '/3.0/campaigns', ['json' => [
                'type' => 'regular',
                'settings' => [
                    'template_id' => 11,
                    'subject_line' => '[Coordinateur Régional] Subject',
                    'title' => 'Full Name - '.date('d/m/Y'),
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
                    'id' => 11,
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
                $this->mailchimpMapping = new MailchimpObjectIdMapping(
                    'main_list_id',
                    'newsletter_list_id',
                    'elected_representative_list_id',
                    'application_request_candidate_list_id',
                    'jecoute_list_id',
                    'jemengage_list_id',
                    'newsletter_legislative_candidate_list_id',
                    'newsletter_renaissance_list_id',
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'senator' => 6,
                    ],
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'senator' => 6,
                        'candidate' => 7,
                        'candidate_jecoute' => 8,
                        'correspondent' => 10,
                        'regional_coordinator' => 11,
                    ],
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
                    [
                        'running_mate' => 123,
                        'volunteer' => 345,
                    ],
                    'https://mailchimp.com',
                    'xyz'
                ),
                new SegmentConditionsBuilder($this->mailchimpMapping, [
                    new AdherentGeoZoneConditionBuilder(),
                    new AdherentInterestConditionBuilder($this->mailchimpMapping),
                    new AdherentRegistrationDateConditionBuilder(),
                    new AdherentSegmentConditionBuilder($this->mailchimpMapping),
                    new AdherentZoneConditionBuilder($this->mailchimpMapping),
                    new CommitteeConditionBuilder($this->mailchimpMapping),
                    new ContactNameConditionBuilder(),
                    new ContactAgeConditionBuilder(),
                    new ContactCityConditionBuilder(),
                    new JecouteConditionBuilder(),
                    new ReferentToAdherentConditionBuilder($this->mailchimpMapping),
                    new ReferentToCandidateConditionBuilder($this->mailchimpMapping),
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

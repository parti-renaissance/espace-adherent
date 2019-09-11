<?php

namespace Tests\AppBundle\AdherentMessage\Handler;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use AppBundle\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use AppBundle\AdherentMessage\MailchimpCampaign\Handler\MunicipalChiefMailchimpCampaignHandler;
use AppBundle\AdherentMessage\MailchimpCampaign\Handler\ReferentMailchimpCampaignHandler;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use AppBundle\Entity\AdherentMessage\Filter\ReferentUserFilter;
use AppBundle\Entity\AdherentMessage\MailchimpCampaign;
use AppBundle\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\District;
use AppBundle\Entity\ReferentTag;
use AppBundle\Mailchimp\Campaign\CampaignContentRequestBuilder;
use AppBundle\Mailchimp\Campaign\CampaignRequestBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\CitizenProjectMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\CommitteeMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\DeputyMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\MunicipalChiefMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\ContentSection\ReferentMessageSectionBuilder;
use AppBundle\Mailchimp\Campaign\Listener\SetCampaignReplyToSubscriber;
use AppBundle\Mailchimp\Campaign\Listener\UpdateCampaignSubjectSubscriber;
use AppBundle\Mailchimp\Campaign\MailchimpObjectIdMapping;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\AdherentZoneConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\CitizenProjectConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\CommitteeConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\ContactNameConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\MunicipalChiefToAdherentConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\MunicipalChiefToCandidateConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToAdherentConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToCandidateConditionBuilder;
use AppBundle\Mailchimp\Campaign\SegmentConditionBuilder\SubscriptionTypeConditionBuilder;
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
    /** @var MailchimpObjectIdMapping */
    private $mailchimpMapping;

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
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'main_list_id',
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
                            'committee_link' => '<a target="_blank" href="https://committee_url" title="Voir le comité">Committee name</a>',
                            'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
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
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'main_list_id',
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
                            'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
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
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'main_list_id',
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
                            'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
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
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'main_list_id',
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

    public function testCitizenProjectMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(CitizenProjectAdherentMessage::class);
        $message->setFilter($filter = new CitizenProjectFilter());
        $filter->setCitizenProject($this->createConfiguredMock(CitizenProject::class, [
            'getName' => 'CP name',
            'getMailchimpId' => 456,
        ]));

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '4',
                        'template_id' => 4,
                        'subject_line' => '[Projet citoyen] Subject',
                        'title' => 'Full Name - '.date('d/m/Y'),
                        'reply_to' => 'projetscitoyens@en-marche.fr',
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'main_list_id',
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
                        'id' => 4,
                        'sections' => [
                            'content' => 'Content',
                            'citizen_project_link' => '<a target="_blank" href="https://citizen_project_url" title="Voir le projet citoyen">CP name</a>',
                            'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                        ],
                    ],
                ]]]
            )
            ->willReturn(new Response(200, [], json_encode(['id' => 123])))
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testMunicipalChiefMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(MunicipalChiefAdherentMessage::class);
        $message->setFilter($filter = new MunicipalChiefFilter([75101, 75102]));
        $filter->setContactRunningMateTeam(true);

        (new MunicipalChiefMailchimpCampaignHandler())->handle($message);

        $this->clientMock
            ->expects($this->exactly(4))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '5',
                        'template_id' => 5,
                        'subject_line' => '[Municipales 2020] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - Paris 1er',
                        'reply_to' => 'jemarche@en-marche.fr',
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'application_request_candidate_list_id',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'TextMerge',
                                    'op' => 'contains',
                                    'field' => 'FVR_CITIES',
                                    'value' => '75101',
                                ],
                                [
                                    'condition_type' => 'TextMerge',
                                    'op' => 'is',
                                    'field' => 'MUNIC_TEAM',
                                    'value' => '75101',
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
                        'id' => 5,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                            'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'city_name' => 'Paris 1er',
                        ],
                    ],
                ]]],
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '5',
                        'template_id' => 5,
                        'subject_line' => '[Municipales 2020] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - Paris 2e',
                        'reply_to' => 'jemarche@en-marche.fr',
                        'from_name' => 'Full Name | La République En Marche !',
                    ],
                    'recipients' => [
                        'list_id' => 'application_request_candidate_list_id',
                        'segment_opts' => [
                            'match' => 'all',
                            'conditions' => [
                                [
                                    'condition_type' => 'TextMerge',
                                    'op' => 'contains',
                                    'field' => 'FVR_CITIES',
                                    'value' => '75102',
                                ],
                                [
                                    'condition_type' => 'TextMerge',
                                    'op' => 'is',
                                    'field' => 'MUNIC_TEAM',
                                    'value' => '75102',
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
                ['PUT', '/3.0/campaigns/campaign_id2/content', ['json' => [
                    'template' => [
                        'id' => 5,
                        'sections' => [
                            'content' => 'Content',
                            'first_name' => 'First Name',
                            'reply_to_link' => '<a title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'reply_to_button' => '<a class="mcnButton" title="Répondre" href="mailto:adherent@mail.com" target="_blank">Répondre</a>',
                            'city_name' => 'Paris 2e',
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
                $this->mailchimpMapping = new MailchimpObjectIdMapping(
                    'main_list_id',
                    'newsletter_list_id',
                    'application_request_candidate_list_id',
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'citizen_project' => 4,
                        'municipal_chief' => 5,
                    ],
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'citizen_project' => 4,
                        'municipal_chief' => 5,
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
                    'C',
                    [
                        'running_mate' => 123,
                        'volunteer' => 345,
                    ]
                ),
                new SegmentConditionsBuilder($this->mailchimpMapping, [
                    new SubscriptionTypeConditionBuilder($this->mailchimpMapping),
                    new ReferentToAdherentConditionBuilder($this->mailchimpMapping),
                    new ReferentToCandidateConditionBuilder($this->mailchimpMapping),
                    new MunicipalChiefToAdherentConditionBuilder($this->mailchimpMapping),
                    new MunicipalChiefToCandidateConditionBuilder($this->mailchimpMapping),
                    new AdherentZoneConditionBuilder($this->mailchimpMapping),
                    new CommitteeConditionBuilder($this->mailchimpMapping),
                    new CitizenProjectConditionBuilder($this->mailchimpMapping),
                    new ContactNameConditionBuilder(),
                ])
            ),
            CampaignContentRequestBuilder::class => new CampaignContentRequestBuilder(
                $this->mailchimpMapping,
                $this->createSectionRequestBuildersLocator()
            ),
        ]);
    }

    private function createSectionRequestBuildersLocator(): ContainerInterface
    {
        return new SimpleContainer([
            CommitteeAdherentMessage::class => new CommitteeMessageSectionBuilder($this->createConfiguredMock(UrlGeneratorInterface::class, ['generate' => 'https://committee_url'])),
            ReferentAdherentMessage::class => new ReferentMessageSectionBuilder(),
            DeputyAdherentMessage::class => new DeputyMessageSectionBuilder(),
            MunicipalChiefAdherentMessage::class => new MunicipalChiefMessageSectionBuilder(),
            CitizenProjectAdherentMessage::class => new CitizenProjectMessageSectionBuilder($this->createConfiguredMock(UrlGeneratorInterface::class, ['generate' => 'https://citizen_project_url'])),
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
                $this->createEventDispatcher(),
                $this->mailchimpMapping
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

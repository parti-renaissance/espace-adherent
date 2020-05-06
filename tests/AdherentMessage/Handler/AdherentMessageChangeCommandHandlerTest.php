<?php

namespace Tests\App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\AdherentZoneMailchimpCampaignHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\MunicipalChiefMailchimpCampaignHandler;
use App\AdherentMessage\MailchimpCampaign\Handler\ReferentMailchimpCampaignHandler;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\MunicipalChiefFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MunicipalChiefAdherentMessage;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Entity\CitizenProject;
use App\Entity\Committee;
use App\Entity\District;
use App\Entity\MunicipalChiefManagedArea;
use App\Entity\ReferentTag;
use App\Mailchimp\Campaign\CampaignContentRequestBuilder;
use App\Mailchimp\Campaign\CampaignRequestBuilder;
use App\Mailchimp\Campaign\ContentSection\CitizenProjectMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\CommitteeMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\DeputyMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\MunicipalChiefMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\ReferentMessageSectionBuilder;
use App\Mailchimp\Campaign\ContentSection\SenatorMessageSectionBuilder;
use App\Mailchimp\Campaign\Listener\SetCampaignReplyToSubscriber;
use App\Mailchimp\Campaign\Listener\UpdateCampaignSubjectSubscriber;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentInterestConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentRegistrationDateConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentSegmentConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\AdherentZoneConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\CitizenProjectConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\CommitteeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactAgeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactCityConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ContactNameConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\MunicipalChiefToAdherentConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\MunicipalChiefToCandidateConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\MunicipalChiefToNewsletterConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToAdherentConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\ReferentToCandidateConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionBuilder\SubscriptionTypeConditionBuilder;
use App\Mailchimp\Campaign\SegmentConditionsBuilder;
use App\Mailchimp\Driver;
use App\Mailchimp\Manager;
use App\Repository\AdherentMessageRepository;
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
                                    'value' => [1],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontains',
                                    'field' => 'interests-A',
                                    'value' => [5, 6],
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
                                    'value' => [1],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 456,
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontains',
                                    'field' => 'interests-A',
                                    'value' => [5, 6],
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

        (new AdherentZoneMailchimpCampaignHandler())->handle($message);

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
                        'title' => 'Full Name - '.date('d/m/Y').' - code1',
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
                                    'value' => [7],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontains',
                                    'field' => 'interests-A',
                                    'value' => [5, 6],
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

    public function testSenatorMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(SenatorAdherentMessage::class);
        $filter = new AdherentZoneFilter($tag = new ReferentTag('Tag1', 'code1')); // 5 and 6 are included by default
        $filter->setIncludeCitizenProjectHosts(false); // exclude 2
        $filter->setIncludeCommitteeSupervisors(false); // exclude 3
        $filter->setIncludeCommitteeHosts(true); // include 4

        $message->setFilter($filter);
        $tag->setExternalId(123);

        (new AdherentZoneMailchimpCampaignHandler())->handle($message);

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '6',
                        'template_id' => 6,
                        'subject_line' => '[Sénateur] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - code1',
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
                                    'value' => [8],
                                ],
                                [
                                    'condition_type' => 'StaticSegment',
                                    'op' => 'static_is',
                                    'field' => 'static_segment',
                                    'value' => 123,
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestcontains',
                                    'field' => 'interests-A',
                                    'value' => [4, 5, 6],
                                ],
                                [
                                    'condition_type' => 'Interests',
                                    'op' => 'interestnotcontains',
                                    'field' => 'interests-A',
                                    'value' => [2, 3],
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
                            'first_name' => 'First Name',
                            'full_name' => 'Full Name',
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
        $message->setFilter($filter = new MunicipalChiefFilter(75101));
        $filter->setContactRunningMateTeam(true);

        (new MunicipalChiefMailchimpCampaignHandler())->handle($message);

        $this->clientMock
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '5',
                        'template_id' => 5,
                        'subject_line' => '[Municipales 2020] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - Paris 1er',
                        'reply_to' => 'ne-pas-repondre@en-marche.fr',
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
                                    'field' => 'FVR_CODES',
                                    'value' => '#75101',
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
                ]]]
            )
            ->willReturn(
                new Response(200, [], json_encode(['id' => 'campaign_id1'])),
                new Response(200, [], json_encode(['id' => 'campaign_id1']))
            )
        ;

        $this->createHandler($message)($this->commandDummy);
    }

    public function testAnnecyMunicipalChiefMessageGeneratesGoodPayloads(): void
    {
        $message = $this->preparedMessage(MunicipalChiefAdherentMessage::class);
        $message->setFilter($filter = new MunicipalChiefFilter(74010));
        $filter->setContactAdherents(true);

        (new MunicipalChiefMailchimpCampaignHandler())->handle($message);

        $this->clientMock
            ->expects($this->exactly(12))
            ->method('request')
            ->withConsecutive(
                ['POST', '/3.0/campaigns', ['json' => [
                    'type' => 'regular',
                    'settings' => [
                        'folder_id' => '5',
                        'template_id' => 5,
                        'subject_line' => '[Municipales 2020] Subject',
                        'title' => 'Full Name - '.date('d/m/Y').' - Annecy',
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
                                    'condition_type' => 'TextMerge',
                                    'op' => 'starts',
                                    'field' => 'CITY',
                                    'value' => 'Annecy (',
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
                        'title' => 'Full Name - '.date('d/m/Y').' - Annecy-le-Vieux',
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
                                    'condition_type' => 'TextMerge',
                                    'op' => 'starts',
                                    'field' => 'CITY',
                                    'value' => 'Annecy-le-Vieux (',
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
                        'title' => 'Full Name - '.date('d/m/Y').' - Seynod',
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
                                    'condition_type' => 'TextMerge',
                                    'op' => 'starts',
                                    'field' => 'CITY',
                                    'value' => 'Seynod (',
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/campaign_id3/content', ['json' => [
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
                        'title' => 'Full Name - '.date('d/m/Y').' - Cran-Gevrier',
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
                                    'condition_type' => 'TextMerge',
                                    'op' => 'starts',
                                    'field' => 'CITY',
                                    'value' => 'Cran-Gevrier (',
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/campaign_id4/content', ['json' => [
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
                        'title' => 'Full Name - '.date('d/m/Y').' - Meythet',
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
                                    'condition_type' => 'TextMerge',
                                    'op' => 'starts',
                                    'field' => 'CITY',
                                    'value' => 'Meythet (',
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/campaign_id5/content', ['json' => [
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
                        'title' => 'Full Name - '.date('d/m/Y').' - Pringy',
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
                                    'condition_type' => 'TextMerge',
                                    'op' => 'starts',
                                    'field' => 'CITY',
                                    'value' => 'Pringy (',
                                ],
                            ],
                        ],
                    ],
                ]]],
                ['PUT', '/3.0/campaigns/campaign_id6/content', ['json' => [
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
            )
            ->willReturn(
                new Response(200, [], json_encode(['id' => 'campaign_id1'])),
                new Response(200, [], json_encode(['id' => 'campaign_id1'])),
                new Response(200, [], json_encode(['id' => 'campaign_id2'])),
                new Response(200, [], json_encode(['id' => 'campaign_id2'])),
                new Response(200, [], json_encode(['id' => 'campaign_id3'])),
                new Response(200, [], json_encode(['id' => 'campaign_id3'])),
                new Response(200, [], json_encode(['id' => 'campaign_id4'])),
                new Response(200, [], json_encode(['id' => 'campaign_id4'])),
                new Response(200, [], json_encode(['id' => 'campaign_id5'])),
                new Response(200, [], json_encode(['id' => 'campaign_id5'])),
                new Response(200, [], json_encode(['id' => 'campaign_id6'])),
                new Response(200, [], json_encode(['id' => 'campaign_id6']))
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
            'getMunicipalChiefManagedArea' => $this->createConfiguredMock(MunicipalChiefManagedArea::class, ['getCityName' => 'Paris 1er']),
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
                        'senator' => 6,
                    ],
                    [
                        'referent' => 1,
                        'deputy' => 2,
                        'committee' => 3,
                        'citizen_project' => 4,
                        'municipal_chief' => 5,
                        'senator' => 6,
                    ],
                    [
                        'subscribed_emails_referents' => 1,
                        'CITIZEN_PROJECT_HOST' => 2,
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
                    [
                        'site_departmental' => 123,
                        'site_municipal' => 456,
                        'main_site' => 789,
                    ]
                ),
                new SegmentConditionsBuilder($this->mailchimpMapping, [
                    new SubscriptionTypeConditionBuilder($this->mailchimpMapping),
                    new ReferentToAdherentConditionBuilder($this->mailchimpMapping),
                    new ReferentToCandidateConditionBuilder($this->mailchimpMapping),
                    new MunicipalChiefToAdherentConditionBuilder($this->mailchimpMapping),
                    new MunicipalChiefToCandidateConditionBuilder($this->mailchimpMapping),
                    new MunicipalChiefToNewsletterConditionBuilder($this->mailchimpMapping),
                    new AdherentZoneConditionBuilder($this->mailchimpMapping),
                    new CommitteeConditionBuilder($this->mailchimpMapping),
                    new CitizenProjectConditionBuilder($this->mailchimpMapping),
                    new ContactNameConditionBuilder(),
                    new AdherentInterestConditionBuilder($this->mailchimpMapping),
                    new AdherentRegistrationDateConditionBuilder(),
                    new AdherentSegmentConditionBuilder($this->mailchimpMapping),
                    new ContactAgeConditionBuilder(),
                    new ContactCityConditionBuilder(),
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
            SenatorAdherentMessage::class => new SenatorMessageSectionBuilder(),
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

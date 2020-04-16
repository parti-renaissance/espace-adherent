<?php

namespace App\Entity;

use MyCLabs\Enum\Enum;

class AdherentCommitmentEnum extends Enum
{
    public const COMMITMENT_SUPPORT_MAJORITY = 'support_majority';
    public const COMMITMENT_INFORM_ME = 'inform_me';
    public const COMMITMENT_PARTICIPATE_MILITANT_ACTION = 'participate_militant_action';
    public const COMMITMENT_RELAY_SOCIAL_CONTENT = 'relay_social_content';
    public const COMMITMENT_FIGHT_FAKE_NEWS = 'fight_fake_news';

    public const IDEAS_MAKING_KNOWN_FRENCH_CONCERNS = 'making_known_french_concerns';
    public const IDEAS_BE_CONSULTED_ON_REFORMS = 'be_consulted_on_reforms';
    public const IDEAS_SHARE_KNOWLEDGE = 'share_knowledge';
    public const IDEAS_WRITE_BACKGROUND_CONTENT = 'write_background_content';

    public const ACTS_ENGAGE_IN_CITIZEN_PROJECTS = 'engage_in_citizen_projects';
    public const ACTS_REINFORCE_POLITIC_COMMITMENT_NEIGHBORHOODS = 'reinforce_politic_commitment_neighborhoods';
    public const ACTS_REINFORCE_POLITIC_COMMITMENT_RURAL_AREAS = 'reinforce_politic_commitment_rural_areas';
    public const ACTS_FAVOR_WOMEN_POLITIC_COMMITMENT = 'favor_women_politic_commitment';
    public const ACTS_TRAIN_ME_TO_ACT = 'train_me_to_act';

    public const PROGESSIVISM_UNDERSTAND_NEXT_ELECTIONS = 'understand_next_elections';
    public const PROGESSIVISM_PARTICIPATE_ELECTION_CAMPAIGN = 'participate_election_campaign';
    public const PROGESSIVISM_MAKE_PROGRAMS = 'make_programs';
    public const PROGESSIVISM_VOTE_BY_PROXY = 'vote_by_proxy';
    public const PROGESSIVISM_BE_ASSESSOR = 'be_assessor';
    public const PROGESSIVISM_PRESENT_MYSELF_TO_ELECTION = 'present_myself_to_election';

    public const COMMITMENT_ACTIONS = [
        self::COMMITMENT_SUPPORT_MAJORITY,
        self::COMMITMENT_INFORM_ME,
        self::COMMITMENT_PARTICIPATE_MILITANT_ACTION,
        self::COMMITMENT_RELAY_SOCIAL_CONTENT,
        self::COMMITMENT_FIGHT_FAKE_NEWS,
    ];

    public const IDEAS_ACTIONS = [
        self::IDEAS_MAKING_KNOWN_FRENCH_CONCERNS,
        self::IDEAS_BE_CONSULTED_ON_REFORMS,
        self::IDEAS_SHARE_KNOWLEDGE,
        self::IDEAS_WRITE_BACKGROUND_CONTENT,
    ];

    public const ACTS_ACTIONS = [
        self::ACTS_ENGAGE_IN_CITIZEN_PROJECTS,
        self::ACTS_REINFORCE_POLITIC_COMMITMENT_NEIGHBORHOODS,
        self::ACTS_REINFORCE_POLITIC_COMMITMENT_RURAL_AREAS,
        self::ACTS_FAVOR_WOMEN_POLITIC_COMMITMENT,
        self::ACTS_TRAIN_ME_TO_ACT,
    ];

    public const PROGRESSIVISM_ACTIONS = [
        self::PROGESSIVISM_UNDERSTAND_NEXT_ELECTIONS,
        self::PROGESSIVISM_PARTICIPATE_ELECTION_CAMPAIGN,
        self::PROGESSIVISM_MAKE_PROGRAMS,
        self::PROGESSIVISM_VOTE_BY_PROXY,
        self::PROGESSIVISM_BE_ASSESSOR,
        self::PROGESSIVISM_PRESENT_MYSELF_TO_ELECTION,
    ];

    public const SKILL_WRITE_ARTICLE = 'write_article';
    public const SKILL_MANAGE_WORKGROUP = 'manage_workgroup';
    public const SKILL_MANAGE_COMMUNITY = 'manage_community';
    public const SKILL_MAKE_VIDEOS = 'make_videos';
    public const SKILL_ANIMATE_COLLECTIVE_MOMENTS = 'animate_collective_moments';
    public const SKILL_WRITE_MEETING_REPORTS = 'write_meeting_reports';
    public const SKILL_MAKE_COMMUNICATION_SUPPORTS = 'make_communication_supports';
    public const SKILL_PREPARE_EVENTS = 'prepare_events';
    public const SKILL_ANIMATE_TRAINING = 'animate_training';
    public const SKILL_WORK_WITH_ELECTED_REPRESENTATIVES = 'work_with_elected_representatives';

    public const SKILLS = [
        self::SKILL_WRITE_ARTICLE,
        self::SKILL_MANAGE_WORKGROUP,
        self::SKILL_MANAGE_COMMUNITY,
        self::SKILL_MAKE_VIDEOS,
        self::SKILL_WRITE_MEETING_REPORTS,
        self::SKILL_MAKE_COMMUNICATION_SUPPORTS,
        self::SKILL_PREPARE_EVENTS,
        self::SKILL_ANIMATE_TRAINING,
        self::SKILL_WORK_WITH_ELECTED_REPRESENTATIVES,
    ];

    public const AVAILABILITY_PUNCTUAL = 'punctual';
    public const AVAILABILITY_MORE_5H_A_MONTH = 'more_5h_a_month';
    public const AVAILABILITY_MORE_5H_A_WEEK = 'more_5h_a_week';

    public const AVAILABILITIES = [
        self::AVAILABILITY_PUNCTUAL,
        self::AVAILABILITY_MORE_5H_A_MONTH,
        self::AVAILABILITY_MORE_5H_A_WEEK,
    ];
}

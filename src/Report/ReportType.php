<?php

namespace App\Report;

use App\Entity\Report\CitizenActionReport;
use App\Entity\Report\CitizenProjectReport;
use App\Entity\Report\CommitteeReport;
use App\Entity\Report\CommunityEventReport;
use App\Entity\Report\IdeasWorkshop\IdeaReport;
use App\Entity\Report\IdeasWorkshop\ThreadCommentReport;
use App\Entity\Report\IdeasWorkshop\ThreadReport;
use App\Entity\Report\ReportableInterface;

final class ReportType
{
    public const CITIZEN_ACTION = 'citizen_action';
    public const CITIZEN_PROJECT = 'citizen_project';
    public const COMMITTEE = 'committee';
    public const COMMUNITY_EVENT = 'community_event';
    public const IDEAS_WORKSHOP_IDEA = 'ideas_workshop_idea';
    public const IDEAS_WORKSHOP_THREAD = 'ideas_workshop_thread';
    public const IDEAS_WORKSHOP_THREAD_COMMENT = 'ideas_workshop_thread_comment';

    public const LIST = [
        self::CITIZEN_PROJECT => CitizenProjectReport::class,
        self::CITIZEN_ACTION => CitizenActionReport::class,
        self::COMMITTEE => CommitteeReport::class,
        self::COMMUNITY_EVENT => CommunityEventReport::class,
        self::IDEAS_WORKSHOP_IDEA => IdeaReport::class,
        self::IDEAS_WORKSHOP_THREAD => ThreadReport::class,
        self::IDEAS_WORKSHOP_THREAD_COMMENT => ThreadCommentReport::class,
    ];

    public const SEARCHABLE_BY_NAME = [
        self::CITIZEN_PROJECT => CitizenProjectReport::class,
        self::CITIZEN_ACTION => CitizenActionReport::class,
        self::COMMITTEE => CommitteeReport::class,
        self::COMMUNITY_EVENT => CommunityEventReport::class,
        self::IDEAS_WORKSHOP_IDEA => IdeaReport::class,
    ];

    public const CITIZEN_ACTION_URI = 'actions-citoyennes';
    public const CITIZEN_PROJECT_URI = 'projets-citoyens';
    public const COMMITTEE_URI = 'comites';
    public const COMMUNITY_EVENT_URI = 'evenements';
    public const IDEAS_WORKSHOP_IDEA_URI = 'atelier-des-idees-notes';
    public const IDEAS_WORKSHOP_THREAD_URI = 'atelier-des-idees-commentaires';
    public const IDEAS_WORKSHOP_THREAD_COMMENT_URI = 'atelier-des-idees-reponses';

    public const TYPES_URI_PATTERN = self::CITIZEN_ACTION_URI
                                     .'|'.self::CITIZEN_PROJECT_URI
                                     .'|'.self::COMMITTEE_URI
                                     .'|'.self::COMMUNITY_EVENT_URI
                                     .'|'.self::IDEAS_WORKSHOP_IDEA_URI
                                     .'|'.self::IDEAS_WORKSHOP_THREAD_URI
                                     .'|'.self::IDEAS_WORKSHOP_THREAD_COMMENT_URI
    ;

    public const URI_MAP = [
        self::CITIZEN_ACTION_URI => self::CITIZEN_ACTION,
        self::CITIZEN_PROJECT_URI => self::CITIZEN_PROJECT,
        self::COMMITTEE_URI => self::COMMITTEE,
        self::COMMUNITY_EVENT_URI => self::COMMUNITY_EVENT,
        self::IDEAS_WORKSHOP_IDEA_URI => self::IDEAS_WORKSHOP_IDEA,
        self::IDEAS_WORKSHOP_THREAD_URI => self::IDEAS_WORKSHOP_THREAD,
        self::IDEAS_WORKSHOP_THREAD_COMMENT_URI => self::IDEAS_WORKSHOP_THREAD_COMMENT,
    ];

    public static function getReportClassForType(string $type): string
    {
        self::assertExists($type);

        return self::LIST[$type];
    }

    public static function getReportClassForSubject(ReportableInterface $subject): string
    {
        return self::getReportClassForType($subject->getReportType());
    }

    /**
     * @throws \LogicException if $type has no URI mapped
     */
    public static function getEntityUriType(ReportableInterface $subject): string
    {
        self::assertExists($type = $subject->getReportType());

        if (false === $uriType = \array_search($type, self::URI_MAP, true)) {
            throw new \LogicException(sprintf('The type "%s" has no uri defined in %s::URI_MAP', $type, __CLASS__));
        }

        return $uriType;
    }

    /**
     * @throws \InvalidArgumentException if $type is not valid
     */
    private static function assertExists(string $type): void
    {
        if (!isset(self::LIST[$type])) {
            throw new \InvalidArgumentException(\sprintf('%s is not a valid ReportType, use %s constants.', $type, __CLASS__));
        }
    }

    /*
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }
}

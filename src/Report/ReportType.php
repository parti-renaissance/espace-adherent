<?php

namespace AppBundle\Report;

use AppBundle\Entity\Report\CitizenActionReport;
use AppBundle\Entity\Report\CitizenProjectReport;
use AppBundle\Entity\Report\CommitteeReport;
use AppBundle\Entity\Report\CommunityEventReport;
use AppBundle\Entity\Report\ReportableInterface;

final class ReportType
{
    public const CITIZEN_ACTION = 'citizen_action';
    public const CITIZEN_PROJECT = 'citizen_project';
    public const COMMITTEE = 'committee';
    public const COMMUNITY_EVENT = 'community_event';

    public const LIST = [
        self::CITIZEN_PROJECT => CitizenProjectReport::class,
        self::CITIZEN_ACTION => CitizenActionReport::class,
        self::COMMITTEE => CommitteeReport::class,
        self::COMMUNITY_EVENT => CommunityEventReport::class,
    ];

    public const CITIZEN_ACTION_URI = 'actions-citoyennes';
    public const CITIZEN_PROJECT_URI = 'projets-citoyens';
    public const COMMITTEE_URI = 'comites';
    public const COMMUNITY_EVENT_URI = 'evenements';

    public const TYPES_URI_PATTERN = self::CITIZEN_ACTION_URI
                                     .'|'.self::CITIZEN_PROJECT_URI
                                     .'|'.self::COMMITTEE_URI
                                     .'|'.self::COMMUNITY_EVENT_URI
    ;

    public const URI_MAP = [
        self::CITIZEN_ACTION_URI => self::CITIZEN_ACTION,
        self::CITIZEN_PROJECT_URI => self::CITIZEN_PROJECT,
        self::COMMITTEE_URI => self::COMMITTEE,
        self::COMMUNITY_EVENT_URI => self::COMMUNITY_EVENT,
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
            throw new \InvalidArgumentException(
                \sprintf('%s is not a valid ReportType, use %s constants.', $type, __CLASS__)
            );
        }
    }

    /*
     * This class should not be instantiated.
     */
    private function __construct()
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Report;

use App\Entity\Report\CommitteeReport;
use App\Entity\Report\CommunityEventReport;
use App\Entity\Report\ReportableInterface;

final class ReportType
{
    public const COMMITTEE = 'committee';
    public const COMMUNITY_EVENT = 'community_event';

    public const LIST = [
        self::COMMITTEE => CommitteeReport::class,
        self::COMMUNITY_EVENT => CommunityEventReport::class,
    ];

    public const SEARCHABLE_BY_NAME = [
        self::COMMITTEE => CommitteeReport::class,
        self::COMMUNITY_EVENT => CommunityEventReport::class,
    ];

    public const COMMITTEE_URI = 'comites';
    public const COMMUNITY_EVENT_URI = 'evenements';

    public const TYPES_URI_PATTERN = self::COMMITTEE_URI.'|'.self::COMMUNITY_EVENT_URI;

    public const URI_MAP = [
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

        if (false === $uriType = array_search($type, self::URI_MAP, true)) {
            throw new \LogicException(\sprintf('The type "%s" has no uri defined in %s::URI_MAP', $type, __CLASS__));
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

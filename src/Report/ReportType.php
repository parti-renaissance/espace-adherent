<?php

namespace AppBundle\Report;

use AppBundle\Entity\CitizenProjectReport;

final class ReportType
{
    public const CITIZEN_PROJECT = 'report.type.citizen_project';

    public const LIST = [
        self::CITIZEN_PROJECT => CitizenProjectReport::class,
    ];

    private function __construct()
    {
    }

    /**
     * @throws \InvalidArgumentException if $type is not valid
     */
    public static function validate(string $type): void
    {
        if (!array_key_exists($type, self::LIST)) {
            throw new \InvalidArgumentException(
                sprintf('%s is not a valid ReportType, use %s constants.', $type, __CLASS__)
            );
        }
    }

    /**
     * @throws \InvalidArgumentException if $type is not valid
     */
    public static function getEntityFQCN(string $type): string
    {
        self::validate($type);

        return self::LIST[$type];
    }
}

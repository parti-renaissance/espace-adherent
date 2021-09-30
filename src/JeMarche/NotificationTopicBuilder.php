<?php

namespace App\JeMarche;

use App\Entity\Geo\Zone;

class NotificationTopicBuilder
{
    private const MAIN_PREFIX = 'jemarche';

    private const PREFIX_ENVIRONMENT_DEV = 'staging';
    private const PREFIX_ENVIRONMENT_PRODUCTION = 'production';

    private const TARGET_GLOBAL = 'global';
    private const PREFIX_TARGET_REGION = 'region';
    private const PREFIX_TARGET_DEPARTMENT = 'department';
    private const PREFIX_TARGET_BOROUGH = 'borough';

    private $environment;
    private $canaryMode;

    public function __construct(string $environment, bool $canaryMode)
    {
        $this->environment = $environment;
        $this->canaryMode = $canaryMode;
    }

    public function buildTopic(?Zone $zone = null): string
    {
        return sprintf('%s_%s', $this->buildTopicPrefix(), $this->getTopic($zone));
    }

    private function getTopic(?Zone $zone): string
    {
        if (!$zone) {
            return self::TARGET_GLOBAL;
        }

        if ($zone->isCity()) {
            $zone = current($zone->getParentsOfType(Zone::DEPARTMENT));
        }

        switch ($zone->getType()) {
            case Zone::REGION:
                return sprintf('%s_%s', self::PREFIX_TARGET_REGION, $zone->getCode());
            case Zone::DEPARTMENT:
                return sprintf('%s_%s', self::PREFIX_TARGET_DEPARTMENT, $zone->getCode());
            case Zone::BOROUGH:
                return sprintf('%s_%s', self::PREFIX_TARGET_BOROUGH, current($zone->getPostalCode()));
            default:
                throw new \InvalidArgumentException('Can not target Zone of type "%s".', $zone->getType());
        }
    }

    private function buildTopicPrefix(): string
    {
        $environmentPrefix = 'prod' === $this->environment && !$this->isCanary()
            ? self::PREFIX_ENVIRONMENT_PRODUCTION
            : self::PREFIX_ENVIRONMENT_DEV
        ;

        return sprintf('%s_%s', $environmentPrefix, self::MAIN_PREFIX);
    }

    private function isCanary(): bool
    {
        return $this->canaryMode;
    }
}

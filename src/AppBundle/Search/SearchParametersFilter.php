<?php

namespace AppBundle\Search;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a way to handle the search parameters.
 */
class SearchParametersFilter
{
    const PARAMETER_QUERY = 'q';
    const PARAMETER_RADIUS = 'r';
    const PARAMETER_CITY = 'c';
    const PARAMETER_TYPE = 't';

    const DEFAULT_TYPE = self::TYPE_COMMITTEES;
    const DEFAULT_RADIUS = self::RADIUS_50;

    const TYPE_COMMITTEES = 'committees';
    const TYPE_EVENTS = 'events';

    const TYPES = [
        self::TYPE_COMMITTEES,
        self::TYPE_EVENTS,
    ];

    const RADIUS_5 = 5;
    const RADIUS_10 = 10;
    const RADIUS_25 = 25;
    const RADIUS_50 = 50;
    const RADIUS_100 = 100;
    const RADIUS_150 = 150;

    const RADII = [
        self::RADIUS_5,
        self::RADIUS_10,
        self::RADIUS_25,
        self::RADIUS_50,
        self::RADIUS_100,
        self::RADIUS_150,
    ];

    private $type;
    private $radius;

    public function __construct()
    {
        $this->type = self::DEFAULT_TYPE;
        $this->radius = self::DEFAULT_RADIUS;
    }

    /**
     * @param Request $request
     */
    public function handleRequest(Request $request)
    {
        $this->setType($request->query->getAlpha(self::PARAMETER_TYPE));
        $this->setRadius($request->query->getInt(self::PARAMETER_RADIUS));
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = in_array($type, self::TYPES, true) ? $type : self::DEFAULT_TYPE;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param int $radius
     */
    public function setRadius(int $radius)
    {
        $this->radius = in_array($radius, self::RADII, true) ? $radius : self::DEFAULT_RADIUS;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }
}

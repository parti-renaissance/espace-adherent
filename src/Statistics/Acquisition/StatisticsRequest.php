<?php

namespace App\Statistics\Acquisition;

use App\Utils\DateTimeFactory;
use Symfony\Component\HttpFoundation\Request;

class StatisticsRequest
{
    private const DATE_FORMAT = 'd-m-Y';

    private $tags;
    private $startDate;
    private $endDate;

    public function __construct(array $tags, ?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate)
    {
        $this->tags = $tags;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public static function createFromHttpRequest(Request $request): self
    {
        if (!$request->query->has('start-date') || !$request->query->has('end-date')) {
            throw new \InvalidArgumentException('start-date or end-date arguments are missing');
        }

        $startDate = DateTimeFactory::create($request->get('start-date'), self::DATE_FORMAT);
        $endDate = DateTimeFactory::create($request->get('end-date'), self::DATE_FORMAT);

        if (!$tags = $request->get('tags')) {
            throw new \InvalidArgumentException('tags argument is missing');
        }

        return new self((array) $tags, $startDate, $endDate);
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function getStartDateAsString(): string
    {
        return $this->startDate->format('Y-m-d 00:00:00');
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function getEndDateAsString(): string
    {
        return $this->endDate->format('Y-m-d 23:59:59');
    }
}

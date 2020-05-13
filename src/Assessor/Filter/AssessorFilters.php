<?php

namespace App\Assessor\Filter;

use App\Exception\AssessorException;
use App\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class AssessorFilters
{
    public const PARAMETER_PAGE = 'page';
    public const PARAMETER_STATUS = 'status';
    public const PARAMETER_VOTE_PLACE = 'votePlace';
    public const PARAMETER_COUNTRY = 'country';
    public const PARAMETER_CITY = 'city';

    public const VOTE_PLACE_CODE_REGEX = '/([0-9]{5}|2[A-Z][0-9]{3})_[0-9]{4}/';

    private const PER_PAGE = 30;

    private $currentPage;
    private $status;
    private $country;
    private $city;
    private $votePlace;

    final private function __construct()
    {
    }

    public static function fromRequest(Request $request)
    {
        $filters = new static();

        if ($status = $request->query->get(self::PARAMETER_STATUS)) {
            $filters->setStatus($status);
        }

        if ($page = $request->query->getInt(self::PARAMETER_PAGE, 1)) {
            $filters->setCurrentPage($page);
        }

        if ($country = $request->query->get(self::PARAMETER_COUNTRY)) {
            $filters->setCountry($country);
        }

        if ($city = $request->query->get(self::PARAMETER_CITY)) {
            $filters->setCity($city);
        }

        if ($votePlace = $request->query->get(self::PARAMETER_VOTE_PLACE)) {
            $filters->setVotePlace($votePlace);
        }

        return $filters;
    }

    public function setCurrentPage(int $page): void
    {
        if ($page < 1) {
            $page = 1;
        }

        $this->currentPage = $page;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    final public function getLimit(): int
    {
        return self::PER_PAGE;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status ?: null;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getVotePlace(): ?string
    {
        return $this->votePlace;
    }

    public function setVotePlace(string $votePlace): void
    {
        $this->votePlace = $votePlace;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        if (empty($country = strtoupper(trim($country)))) {
            $this->country = null;

            return;
        }

        if (!\in_array($country, array_keys(UnitedNationsBundle::getCountries()), true)) {
            throw new AssessorException(sprintf('Invalid country filter value given ("%s").', $country));
        }

        $this->country = $country;
    }

    public function getCountries()
    {
        return UnitedNationsBundle::getCountries();
    }

    public function hasData(): bool
    {
        return $this->country || $this->city || $this->votePlace;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        $qb
            ->setFirstResult(($this->currentPage - 1) * self::PER_PAGE)
            ->setMaxResults(self::PER_PAGE)
        ;
    }

    final public function toQueryString(): string
    {
        return http_build_query($this->getQueryStringParameters());
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
    }
}

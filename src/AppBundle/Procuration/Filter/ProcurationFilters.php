<?php

namespace AppBundle\Procuration\Filter;

use AppBundle\Exception\ProcurationException;
use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class ProcurationFilters
{
    const PER_PAGE = 30;

    const PARAMETER_CITY = 'city';
    const PARAMETER_COUNTRY = 'country';
    const PARAMETER_PAGE = 'page';
    const PARAMETER_TYPE = 'type';
    const PARAMETER_STATUS = 'status';

    const TYPE_PRESIDENTIAL_1_ROUND = 'electionPresidentialFirstRound';
    const TYPE_PRESIDENTIAL_2_ROUND = 'electionPresidentialSecondRound';
    const TYPE_LEGISLATIVE_1_ROUND = 'electionLegislativeFirstRound';
    const TYPE_LEGISLATIVE_2_ROUND = 'electionLegislativeSecondRound';

    const TYPES = [
        self::TYPE_PRESIDENTIAL_1_ROUND => 'Présidentielle : 1er tour',
        self::TYPE_PRESIDENTIAL_2_ROUND => 'Présidentielle : 2nd tour',
        self::TYPE_LEGISLATIVE_1_ROUND => 'Législatives : 1er tour',
        self::TYPE_LEGISLATIVE_2_ROUND => 'Législatives : 2nd tour',
    ];

    private $currentPage;
    private $country;
    private $city;
    private $type;
    private $status;

    final private function __construct()
    {
    }

    public static function fromQueryString(Request $request)
    {
        $filters = new static();

        if ($country = $request->query->get(self::PARAMETER_COUNTRY)) {
            $filters->setCountry($country);
        }

        if ($city = $request->query->get(self::PARAMETER_CITY)) {
            $filters->setCity($city);
        }

        if ($type = $request->query->get(self::PARAMETER_TYPE)) {
            $filters->setType($type);
        }

        if ($status = $request->query->get(self::PARAMETER_STATUS)) {
            $filters->setStatus($status);
        }

        if ($page = $request->query->getInt(self::PARAMETER_PAGE, 1)) {
            $filters->setCurrentPage($page);
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

    public function setCountry(string $country): void
    {
        if (empty($country = strtoupper(trim($country)))) {
            $this->country = null;

            return;
        }

        if (!in_array($country, array_keys($this->getCountries()), true)) {
            throw new ProcurationException(sprintf('Invalid country filter value given ("%s").', $country));
        }

        $this->country = trim($country);
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCity(string $city): void
    {
        $this->city = trim($city);
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setType(string $type): void
    {
        $this->type = isset(self::TYPES[$type]) ? $type : null;
    }

    public function setStatus(string $status): void
    {
        if (empty($status)) {
            $this->status = null;

            return;
        }

        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTypes(): array
    {
        return self::TYPES;
    }

    public function getCountries(): array
    {
        return UnitedNationsBundle::getCountries();
    }

    public function hasData(): bool
    {
        return $this->country || $this->city || $this->type;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        if ($this->country) {
            $qb->andWhere(sprintf('%s.voteCountry = :filterVotreCountry', $alias));
            $qb->setParameter('filterVotreCountry', $this->country);
        }

        if ($this->city) {
            if (is_numeric($this->city)) {
                $qb->andWhere(sprintf('%s.votePostalCode LIKE :filterVoteCity', $alias));
                $qb->setParameter('filterVoteCity', $this->city.'%');
            } else {
                $qb->andWhere(sprintf('LOWER(%s.voteCityName) LIKE :filterVoteCity', $alias));
                $qb->setParameter('filterVoteCity', '%'.strtolower($this->city).'%');
            }
        }

        if ($this->type) {
            $qb->andWhere(sprintf('%s.%s = true', $alias, $this->type));
        }

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
        if ($this->country) {
            $parameters[self::PARAMETER_COUNTRY] = mb_strtolower($this->country);
        }

        if ($this->city) {
            $parameters[self::PARAMETER_CITY] = $this->city;
        }

        if ($this->type) {
            $parameters[self::PARAMETER_TYPE] = $this->type;
        }

        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        return $parameters ?? [];
    }
}

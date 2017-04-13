<?php

namespace AppBundle\Search;

use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a way to handle the search parameters.
 */
class ProcurationParametersFilter
{
    const PARAMETER_COUNTRY = 'p';
    const PARAMETER_CITY = 'v';
    const PARAMETER_TYPE = 't';

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

    private $country = null;
    private $city = null;
    private $type = null;

    public function handleRequest(Request $request): self
    {
        $this->setCountry((string) $request->query->get(self::PARAMETER_COUNTRY));
        $this->setCity((string) $request->query->get(self::PARAMETER_CITY));
        $this->setType((string) $request->query->get(self::PARAMETER_TYPE));

        return $this;
    }

    public function setCountry(string $country): void
    {
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
    }
}

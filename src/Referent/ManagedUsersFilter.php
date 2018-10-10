<?php

namespace AppBundle\Referent;

use AppBundle\Entity\ReferentManagedUsersMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    const PER_PAGE = 50;

    const PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE = 'anc';
    const PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE = 'aic';
    const PARAMETER_INCLUDE_HOSTS = 'h';
    const PARAMETER_INCLUDE_SUPERVISORS = 's';
    const PARAMETER_QUERY_AREA_CODE = 'ac';
    const PARAMETER_QUERY_CITY = 'city';
    const PARAMETER_QUERY_ID = 'id';
    const PARAMETER_OFFSET = 'o';
    const PARAMETER_TOKEN = 't';
    const PARAMETER_GENDER = 'g';
    const PARAMETER_LAST_NAME = 'l';
    const PARAMETER_FIRST_NAME = 'f';
    const PARAMETER_AGE_MIN = 'amin';
    const PARAMETER_AGE_MAX = 'amax';

    private $includeAdherentsNoCommittee = true;
    private $includeAdherentsInCommittee = true;
    private $includeHosts = true;
    private $includeSupevisors = true;

    /**
     * @Assert\NotNull
     */
    private $queryAreaCode = '';

    /**
     * @Assert\NotNull
     */
    private $queryCity = '';

    /**
     * @Assert\NotNull
     */
    private $queryId = '';

    /**
     * @Assert\NotNull
     */
    private $offset = 0;
    private $token = '';
    private $queryGender = '';
    private $queryLastName = '';
    private $queryFirstName = '';
    private $queryAgeMinimum = 0;
    private $queryAgeMaximum = 0;

    public static function createFromMessage(ReferentManagedUsersMessage $message): self
    {
        $filter = new self();
        $filter->includeAdherentsNoCommittee = $message->includeAdherentsNoCommittee();
        $filter->includeAdherentsInCommittee = $message->includeAdherentsInCommittee();
        $filter->includeHosts = $message->includeHosts();
        $filter->includeSupevisors = $message->includeSupevisors();
        $filter->queryAreaCode = $message->getQueryAreaCode();
        $filter->queryCity = $message->getQueryCity();
        $filter->queryId = $message->getQueryId();
        $filter->offset = $message->getOffset();

        return $filter;
    }

    /**
     * @return ManagedUsersFilter
     */
    public function handleRequest(Request $request): self
    {
        $query = $request->query;
        if (0 === $query->count()) {
            return $this;
        }

        $this->includeAdherentsNoCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE);
        $this->includeAdherentsInCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE);
        $this->includeHosts = $query->getBoolean(self::PARAMETER_INCLUDE_HOSTS);
        $this->includeSupevisors = $query->getBoolean(self::PARAMETER_INCLUDE_SUPERVISORS);
        $this->queryAreaCode = trim($query->get(self::PARAMETER_QUERY_AREA_CODE, ''));
        $this->queryCity = trim($query->get(self::PARAMETER_QUERY_CITY, ''));
        $this->queryId = trim($query->get(self::PARAMETER_QUERY_ID, ''));
        $this->offset = $query->getInt(self::PARAMETER_OFFSET);
        $this->token = $query->get(self::PARAMETER_TOKEN, '');
        $this->queryGender = $query->get(self::PARAMETER_GENDER, '');
        $this->queryLastName = $query->get(self::PARAMETER_LAST_NAME, '');
        $this->queryFirstName = $query->get(self::PARAMETER_FIRST_NAME, '');
        $this->queryAgeMinimum = $query->getInt(self::PARAMETER_AGE_MIN);
        $this->queryAgeMaximum = $query->getInt(self::PARAMETER_AGE_MAX);

        return $this;
    }

    public function __toString()
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public function getQueryStringForOffset(int $offset): string
    {
        return '?'.http_build_query([
            self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE => $this->includeAdherentsNoCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE => $this->includeAdherentsInCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_HOSTS => $this->includeHosts ? '1' : '0',
            self::PARAMETER_INCLUDE_SUPERVISORS => $this->includeSupevisors ? '1' : '0',
            self::PARAMETER_QUERY_AREA_CODE => $this->queryAreaCode ?: '',
            self::PARAMETER_QUERY_CITY => $this->queryCity ?: '',
            self::PARAMETER_QUERY_ID => $this->queryId ?: '',
            self::PARAMETER_OFFSET => $offset,
            self::PARAMETER_TOKEN => $this->token,
            self::PARAMETER_GENDER => $this->queryGender,
            self::PARAMETER_LAST_NAME => $this->queryLastName,
            self::PARAMETER_FIRST_NAME => $this->queryFirstName,
            self::PARAMETER_AGE_MIN => $this->queryAgeMinimum,
            self::PARAMETER_AGE_MAX => $this->queryAgeMaximum,
        ]);
    }

    public function getPreviousPageQueryString(): string
    {
        $previousOffset = $this->offset - self::PER_PAGE;

        return $this->getQueryStringForOffset($previousOffset >= 0 ? $previousOffset : 0);
    }

    public function getNextPageQueryString(): string
    {
        return $this->getQueryStringForOffset($this->offset + self::PER_PAGE);
    }

    public function getQueryAreaCode(): string
    {
        return $this->queryAreaCode;
    }

    public function getQueryCity(): string
    {
        return $this->queryCity;
    }

    public function getQueryId(): string
    {
        return $this->queryId;
    }

    public function includeAdherentsNoCommittee(): bool
    {
        return $this->includeAdherentsNoCommittee;
    }

    public function includeAdherentsInCommittee(): bool
    {
        return $this->includeAdherentsInCommittee;
    }

    public function includeHosts(): bool
    {
        return $this->includeHosts;
    }

    public function includeSupervisors(): bool
    {
        return $this->includeSupevisors;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function hasToken(): bool
    {
        return !empty($this->token);
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getQueryGender(): ?string
    {
        return $this->queryGender;
    }

    public function setQueryGender(?string $queryGender): void
    {
        $this->queryGender = $queryGender;
    }

    public function getQueryLastName(): ?string
    {
        return $this->queryLastName;
    }

    public function setQueryLastName(?string $queryLastName): void
    {
        $this->queryLastName = $queryLastName;
    }

    public function getQueryFirstName(): ?string
    {
        return $this->queryFirstName;
    }

    public function setQueryFirstName(?string $queryFirstName): void
    {
        $this->queryFirstName = $queryFirstName;
    }

    public function getQueryAgeMinimum(): int
    {
        return $this->queryAgeMinimum;
    }

    public function setQueryAgeMinimum(int $queryAgeMinimum): void
    {
        $this->queryAgeMinimum = $queryAgeMinimum;
    }

    public function getQueryAgeMaximum(): int
    {
        return $this->queryAgeMaximum;
    }

    public function setQueryAgeMaximum(int $queryAgeMaximum): void
    {
        $this->queryAgeMaximum = $queryAgeMaximum;
    }
}

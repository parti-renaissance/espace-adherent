<?php

namespace AppBundle\Referent;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    const PER_PAGE = 50;

    const PARAMETER_INCLUDE_NEWSLETTER = 'n';
    const PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE = 'anc';
    const PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE = 'aic';
    const PARAMETER_INCLUDE_HOSTS = 'h';
    const PARAMETER_INCLUDE_SUPERVISORS = 's';
    const PARAMETER_QUERY_AREA_CODE = 'ac';
    const PARAMETER_QUERY_CITY = 'city';
    const PARAMETER_QUERY_ID = 'id';
    const PARAMETER_OFFSET = 'o';
    const PARAMETER_TOKEN = 't';

    private $includeNewsletter = true;
    private $includeAdherentsNoCommittee = true;
    private $includeAdherentsInCommittee = true;
    private $includeHosts = true;
    private $includeSupevisors = true;
    private $queryAreaCode = '';
    private $queryCity = '';
    private $queryId = '';
    private $offset = 0;
    private $token = '';

    public static function createFromArray(array $data): self
    {
        $filter = new self();
        $filter->includeNewsletter = $data[self::PARAMETER_INCLUDE_NEWSLETTER] ?? true;
        $filter->includeAdherentsNoCommittee = $data[self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE] ?? true;
        $filter->includeAdherentsInCommittee = $data[self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE] ?? true;
        $filter->includeHosts = $data[self::PARAMETER_INCLUDE_HOSTS] ?? true;
        $filter->includeSupevisors = $data[self::PARAMETER_INCLUDE_SUPERVISORS] ?? true;
        $filter->queryAreaCode = $data[self::PARAMETER_QUERY_AREA_CODE] ?? '';
        $filter->queryCity = $data[self::PARAMETER_QUERY_CITY] ?? '';
        $filter->queryId = $data[self::PARAMETER_QUERY_ID] ?? '';
        $filter->offset = $data[self::PARAMETER_OFFSET] ?? 0;

        return $filter;
    }

    public function toArray(): array
    {
        return [
            self::PARAMETER_INCLUDE_NEWSLETTER => $this->includeNewsletter,
            self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE => $this->includeAdherentsNoCommittee,
            self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE => $this->includeAdherentsInCommittee,
            self::PARAMETER_INCLUDE_HOSTS => $this->includeHosts,
            self::PARAMETER_INCLUDE_SUPERVISORS => $this->includeSupevisors,
            self::PARAMETER_QUERY_AREA_CODE => $this->queryAreaCode,
            self::PARAMETER_QUERY_CITY => $this->queryCity,
            self::PARAMETER_QUERY_ID => $this->queryId,
            self::PARAMETER_OFFSET => $this->offset,
        ];
    }

    /**
     * @param Request $request
     *
     * @return ManagedUsersFilter
     */
    public function handleRequest(Request $request): self
    {
        $query = $request->query;
        if (0 === $query->count()) {
            return $this;
        }

        $this->includeNewsletter = $query->getBoolean(self::PARAMETER_INCLUDE_NEWSLETTER);
        $this->includeAdherentsNoCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE);
        $this->includeAdherentsInCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE);
        $this->includeHosts = $query->getBoolean(self::PARAMETER_INCLUDE_HOSTS);
        $this->includeSupevisors = $query->getBoolean(self::PARAMETER_INCLUDE_SUPERVISORS);
        $this->queryAreaCode = trim($query->get(self::PARAMETER_QUERY_AREA_CODE, ''));
        $this->queryCity = trim($query->get(self::PARAMETER_QUERY_CITY, ''));
        $this->queryId = trim($query->get(self::PARAMETER_QUERY_ID, ''));
        $this->offset = $query->getInt(self::PARAMETER_OFFSET);
        $this->token = $query->get(self::PARAMETER_TOKEN, '');

        return $this;
    }

    public function __toString()
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public function getQueryStringForOffset(int $offset): string
    {
        return '?'.http_build_query([
            self::PARAMETER_INCLUDE_NEWSLETTER => $this->includeNewsletter ? '1' : '0',
            self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE => $this->includeAdherentsNoCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE => $this->includeAdherentsInCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_HOSTS => $this->includeHosts ? '1' : '0',
            self::PARAMETER_INCLUDE_SUPERVISORS => $this->includeSupevisors ? '1' : '0',
            self::PARAMETER_QUERY_AREA_CODE => $this->queryAreaCode ?: '',
            self::PARAMETER_QUERY_CITY => $this->queryCity ?: '',
            self::PARAMETER_QUERY_ID => $this->queryId ?: '',
            self::PARAMETER_OFFSET => $offset,
            self::PARAMETER_TOKEN => $this->token,
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

    public function includeNewsletter(): bool
    {
        return $this->includeNewsletter;
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
}

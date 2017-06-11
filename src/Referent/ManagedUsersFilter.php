<?php

namespace AppBundle\Referent;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a way to handle the search parameters.
 */
class ManagedUsersFilter
{
    const PER_PAGE = 50;

    const PARAMETER_QUERY = 'q';
    const PARAMETER_INCLUDE_NEWSLETTER = 'n';
    const PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE = 'anc';
    const PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE = 'aic';
    const PARAMETER_INCLUDE_HOSTS = 'h';
    const PARAMETER_OFFSET = 'o';
    const PARAMETER_TOKEN = 't';

    private $query = '';
    private $includeNewsletter = true;
    private $includeAdherentsNoCommittee = true;
    private $includeAdherentsInCommittee = true;
    private $includeHosts = true;
    private $offset = 0;
    private $token = '';

    public static function createFromArray(array $data): self
    {
        $filter = new self();
        $filter->query = $data[self::PARAMETER_QUERY] ?? '';
        $filter->includeNewsletter = $data[self::PARAMETER_INCLUDE_NEWSLETTER] ?? true;
        $filter->includeAdherentsNoCommittee = $data[self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE] ?? true;
        $filter->includeAdherentsInCommittee = $data[self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE] ?? true;
        $filter->includeHosts = $data[self::PARAMETER_INCLUDE_HOSTS] ?? true;
        $filter->offset = $data[self::PARAMETER_OFFSET] ?? true;

        return $filter;
    }

    public function toArray(): array
    {
        return [
            self::PARAMETER_QUERY => $this->query,
            self::PARAMETER_INCLUDE_NEWSLETTER => $this->includeNewsletter,
            self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE => $this->includeAdherentsNoCommittee,
            self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE => $this->includeAdherentsInCommittee,
            self::PARAMETER_INCLUDE_HOSTS => $this->includeHosts,
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

        $this->query = trim($query->get(self::PARAMETER_QUERY, ''));
        $this->includeNewsletter = $query->getBoolean(self::PARAMETER_INCLUDE_NEWSLETTER);
        $this->includeAdherentsNoCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE);
        $this->includeAdherentsInCommittee = $query->getBoolean(self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE);
        $this->includeHosts = $query->getBoolean(self::PARAMETER_INCLUDE_HOSTS);
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
            self::PARAMETER_QUERY => $this->query ?: '',
            self::PARAMETER_INCLUDE_NEWSLETTER => $this->includeNewsletter ? '1' : '0',
            self::PARAMETER_INCLUDE_ADHERENTS_NO_COMMITTEE => $this->includeAdherentsNoCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_ADHERENTS_IN_COMMITTEE => $this->includeAdherentsInCommittee ? '1' : '0',
            self::PARAMETER_INCLUDE_HOSTS => $this->includeHosts ? '1' : '0',
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

    public function getQuery(): string
    {
        return $this->query;
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

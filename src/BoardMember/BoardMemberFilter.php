<?php

namespace App\BoardMember;

use Symfony\Component\HttpFoundation\Request;

class BoardMemberFilter
{
    const PER_PAGE = 50;

    const PARAMETER_GENDER = 'g';
    const PARAMETER_AGE_MIN = 'amin';
    const PARAMETER_AGE_MAX = 'amax';
    const PARAMETER_FIRST_NAME = 'f';
    const PARAMETER_LAST_NAME = 'l';
    const PARAMETER_POSTAL_CODE = 'p';
    const PARAMETER_AREAS = 'a';
    const PARAMETER_ROLES = 'r';
    const PARAMETER_OFFSET = 'o';
    const PARAMETER_TOKEN = 't';

    private $queryGender = '';
    private $queryAgeMinimum = 0;
    private $queryAgeMaximum = 0;
    private $queryFirstName = '';
    private $queryLastName = '';
    private $queryPostalCode = '';
    private $queryAreas = [];
    private $queryRoles = [];
    private $offset = 0;
    private $token = '';

    public static function createFromArray(array $data): self
    {
        $filter = new self();
        $filter->queryGender = $data[self::PARAMETER_GENDER] ?? '';
        $filter->queryAgeMinimum = $data[self::PARAMETER_AGE_MIN] ?? 0;
        $filter->queryAgeMaximum = $data[self::PARAMETER_AGE_MAX] ?? 0;
        $filter->queryFirstName = $data[self::PARAMETER_FIRST_NAME] ?? '';
        $filter->queryLastName = $data[self::PARAMETER_LAST_NAME] ?? '';
        $filter->queryPostalCode = $data[self::PARAMETER_POSTAL_CODE] ?? '';
        $filter->queryAreas = $data[self::PARAMETER_AREAS] ?? [];
        $filter->queryRoles = $data[self::PARAMETER_ROLES] ?? [];
        $filter->offset = $data[self::PARAMETER_OFFSET] ?? 0;

        return $filter;
    }

    public function toArray(): array
    {
        return [
            self::PARAMETER_GENDER => $this->queryGender,
            self::PARAMETER_AGE_MIN => $this->queryAgeMinimum,
            self::PARAMETER_AGE_MAX => $this->queryAgeMaximum,
            self::PARAMETER_FIRST_NAME => $this->queryFirstName,
            self::PARAMETER_LAST_NAME => $this->queryLastName,
            self::PARAMETER_POSTAL_CODE => $this->queryPostalCode,
            self::PARAMETER_AREAS => $this->queryAreas,
            self::PARAMETER_ROLES => $this->queryRoles,
            self::PARAMETER_OFFSET => $this->offset,
        ];
    }

    public function handleRequest(Request $request): self
    {
        $query = $request->query;
        if (0 === $query->count()) {
            return $this;
        }

        $this->queryGender = $query->get(self::PARAMETER_GENDER, '');
        $this->queryAgeMinimum = $query->getInt(self::PARAMETER_AGE_MIN);
        $this->queryAgeMaximum = $query->getInt(self::PARAMETER_AGE_MAX);
        $this->queryFirstName = $query->get(self::PARAMETER_FIRST_NAME, '');
        $this->queryLastName = $query->get(self::PARAMETER_LAST_NAME, '');
        $this->queryPostalCode = $query->get(self::PARAMETER_POSTAL_CODE, '');
        $this->queryAreas = $query->get(self::PARAMETER_AREAS, []);
        $this->queryRoles = $query->get(self::PARAMETER_ROLES, []);
        $this->offset = $query->getInt(self::PARAMETER_OFFSET);
        $this->token = $query->get(self::PARAMETER_TOKEN, '');

        return $this;
    }

    public function __toString(): string
    {
        return $this->getQueryStringForOffset($this->offset);
    }

    public function getQueryStringForOffset(int $offset): string
    {
        return '?'.http_build_query([
            self::PARAMETER_GENDER => $this->queryGender,
            self::PARAMETER_AGE_MIN => $this->queryAgeMinimum,
            self::PARAMETER_AGE_MAX => $this->queryAgeMaximum,
            self::PARAMETER_FIRST_NAME => $this->queryFirstName,
            self::PARAMETER_LAST_NAME => $this->queryLastName,
            self::PARAMETER_POSTAL_CODE => $this->queryPostalCode,
            self::PARAMETER_AREAS => $this->queryAreas,
            self::PARAMETER_ROLES => $this->queryRoles,
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

    public function getQueryGender(): string
    {
        return $this->queryGender;
    }

    public function getQueryAgeMinimum(): ?int
    {
        return $this->queryAgeMinimum;
    }

    public function getQueryAgeMaximum(): ?int
    {
        return $this->queryAgeMaximum;
    }

    public function getQueryFirstName(): string
    {
        return $this->queryFirstName;
    }

    public function getQueryLastName(): string
    {
        return $this->queryLastName;
    }

    public function getQueryPostalCode(): string
    {
        return $this->queryPostalCode;
    }

    public function getQueryAreas(): array
    {
        return $this->queryAreas;
    }

    public function getQueryRoles(): array
    {
        return $this->queryRoles;
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

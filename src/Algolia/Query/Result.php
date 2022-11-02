<?php

namespace App\Algolia\Query;

class Result implements \Countable, \Iterator
{
    /**
     * @var array
     */
    private $hits;

    /**
     * @var int|null
     */
    private $nbHits;

    /**
     * @var int|null
     */
    private $page;

    /**
     * @var int|null
     */
    private $nbPages;

    /**
     * @var int|null
     */
    private $hitsPerPage;

    public static function create(array $response): self
    {
        $result = new self();
        $result->hits = $response['hits'];
        $result->nbHits = $response['nbHits'];
        $result->page = $response['page'];
        $result->nbPages = $response['nbPages'];
        $result->hitsPerPage = $response['hitsPerPage'];

        return $result;
    }

    public function getHits(): array
    {
        return $this->hits;
    }

    public function getNbHits(): ?int
    {
        return $this->nbHits;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getNbPages(): ?int
    {
        return $this->nbPages;
    }

    public function getHitsPerPage(): ?int
    {
        return $this->hitsPerPage;
    }

    public function count(): int
    {
        return \count($this->hits);
    }

    public function current(): mixed
    {
        return current($this->hits);
    }

    public function next(): void
    {
        next($this->hits);
    }

    public function key(): mixed
    {
        return key($this->hits);
    }

    public function valid(): bool
    {
        return null !== key($this->hits);
    }

    public function rewind(): void
    {
        reset($this->hits);
    }
}

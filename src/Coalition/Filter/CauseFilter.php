<?php

namespace App\Coalition\Filter;

use App\Entity\Coalition\Cause;
use Symfony\Component\Validator\Constraints as Assert;

class CauseFilter
{
    /**
     * @var string
     *
     * @Assert\Choice(choices=Cause::STATUSES, strict=true)
     */
    private $status;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"name"})
     */
    private $sort = 'name';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'a';

    public function __construct(string $status = null)
    {
        $this->status = $status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status ?: null;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'sort' => $this->sort,
            'order' => $this->order,
        ];
    }
}

<?php

namespace App\Coalition\Filter;

use App\Entity\Coalition\Cause;
use App\Entity\Coalition\Coalition;
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
     * @var Coalition|null
     *
     * @Assert\Type(Coalition::class)
     */
    private $primaryCoalition;

    /**
     * @var Coalition|null
     *
     * @Assert\Type(Coalition::class)
     */
    private $secondaryCoalition;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $authorFirstName;

    /**
     * @var string|null
     */
    private $authorLastName;

    /**
     * @var \DateTimeInterface|null
     *
     * @Assert\DateTime
     */
    private $createdAfter;

    /**
     * @var \DateTimeInterface|null
     *
     * @Assert\DateTime
     */
    private $createdBefore;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"id"})
     */
    private $sort = 'id';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'd';

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

    public function getPrimaryCoalition(): ?Coalition
    {
        return $this->primaryCoalition;
    }

    public function setPrimaryCoalition(?Coalition $primaryCoalition): void
    {
        $this->primaryCoalition = $primaryCoalition;
    }

    public function getSecondaryCoalition(): ?Coalition
    {
        return $this->secondaryCoalition;
    }

    public function setSecondaryCoalition(?Coalition $secondaryCoalition): void
    {
        $this->secondaryCoalition = $secondaryCoalition;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getAuthorFirstName(): ?string
    {
        return $this->authorFirstName;
    }

    public function setAuthorFirstName(?string $authorFirstName): void
    {
        $this->authorFirstName = $authorFirstName;
    }

    public function getAuthorLastName(): ?string
    {
        return $this->authorLastName;
    }

    public function setAuthorLastName(?string $authorLastName): void
    {
        $this->authorLastName = $authorLastName;
    }

    public function getCreatedAfter(): ?\DateTimeInterface
    {
        return $this->createdAfter;
    }

    public function setCreatedAfter(?\DateTimeInterface $createdAfter): void
    {
        $this->createdAfter = $createdAfter;
    }

    public function getCreatedBefore(): ?\DateTimeInterface
    {
        return $this->createdBefore;
    }

    public function setCreatedBefore(?\DateTimeInterface $createdBefore): void
    {
        $this->createdBefore = $createdBefore;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'primaryCoalition' => $this->primaryCoalition ? $this->primaryCoalition->getId() : null,
            'secondaryCoalition' => $this->secondaryCoalition ? $this->secondaryCoalition->getId() : null,
            'name' => $this->name,
            'authorFirstName' => $this->authorFirstName,
            'authorLastName' => $this->authorLastName,
            'createdAfter' => $this->createdAfter ? $this->createdAfter->format('Y-m-d') : null,
            'createdBefore' => $this->createdBefore ? $this->createdBefore->format('Y-m-d') : null,
            'sort' => $this->sort,
            'order' => $this->order,
        ];
    }
}

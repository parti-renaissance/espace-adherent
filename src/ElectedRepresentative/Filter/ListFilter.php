<?php

namespace App\ElectedRepresentative\Filter;

use App\Entity\ReferentTag;
use Symfony\Component\Validator\Constraints as Assert;

class ListFilter
{
    /**
     * @var ReferentTag[]
     *
     * @Assert\NotNull
     */
    private $referentTags = [];

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"lastName"})
     */
    private $sort = 'lastName';

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices={"a", "d"})
     */
    private $order = 'a';

    public function __construct(array $referentTags = [])
    {
        $this->referentTags = $referentTags;
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags;
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags[] = $referentTag;
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        foreach ($this->referentTags as $key => $tag) {
            if ($tag->getId() === $referentTag->getId()) {
                unset($this->referentTags[$key]);
            }
        }
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
            'referentTags' => 1 === \count($this->referentTags) ? current($this->referentTags)->getId() : null,
            'sort' => $this->sort,
            'order' => $this->order,
        ];
    }
}

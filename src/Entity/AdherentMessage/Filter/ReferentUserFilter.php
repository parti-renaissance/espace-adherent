<?php

namespace App\Entity\AdherentMessage\Filter;

use App\Entity\ReferentTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class ReferentUserFilter extends AbstractUserFilter
{
    use BasicUserFiltersTrait;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $contactOnlyVolunteers = false;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $contactOnlyRunningMates = false;

    /**
     * @var ReferentTag[]|Collection
     *
     * @Assert\NotNull
     */
    #[ORM\ManyToMany(targetEntity: ReferentTag::class)]
    private $referentTags;

    public function __construct(array $referentTags)
    {
        parent::__construct();

        $this->referentTags = new ArrayCollection();

        foreach ($referentTags as $tag) {
            $this->addReferentTag($tag);
        }
    }

    /**
     * @return ReferentTag[]
     */
    public function getReferentTags(): array
    {
        return $this->referentTags->toArray();
    }

    public function addReferentTag(ReferentTag $referentTag): void
    {
        if (!$this->referentTags->contains($referentTag)) {
            $this->referentTags->add($referentTag);
        }
    }

    public function removeReferentTag(ReferentTag $referentTag): void
    {
        $this->referentTags->removeElement($referentTag);
    }

    public function getContactOnlyVolunteers(): bool
    {
        return $this->contactOnlyVolunteers;
    }

    public function setContactOnlyVolunteers(bool $contactOnlyVolunteers): void
    {
        $this->contactOnlyVolunteers = $contactOnlyVolunteers;
    }

    public function getContactOnlyRunningMates(): bool
    {
        return $this->contactOnlyRunningMates;
    }

    public function setContactOnlyRunningMates(bool $contactOnlyRunningMates): void
    {
        $this->contactOnlyRunningMates = $contactOnlyRunningMates;
    }
}

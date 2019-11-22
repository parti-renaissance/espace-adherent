<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class SenatorArea
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var ReferentTag|null
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReferentTag")
     */
    private $departmentTag;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $entireWorld = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDepartmentTag(): ?ReferentTag
    {
        return $this->departmentTag;
    }

    public function setDepartmentTag(?ReferentTag $departmentTag): void
    {
        $this->departmentTag = $departmentTag;
    }

    public function isEntireWorld(): bool
    {
        return $this->entireWorld;
    }

    public function setEntireWorld(bool $entireWorld): void
    {
        $this->entireWorld = $entireWorld;
    }

    /**
     * @Assert\IsTrue(message="senator_area.invalid_area_choice")
     */
    public function isValid(): bool
    {
        return ($this->departmentTag && !$this->entireWorld)
            || ($this->entireWorld && !$this->departmentTag)
        ;
    }
}

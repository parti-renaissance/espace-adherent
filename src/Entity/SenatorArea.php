<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

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
}

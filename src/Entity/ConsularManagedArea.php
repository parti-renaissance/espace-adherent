<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ConsularManagedArea
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
     * @var ConsularDistrict
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ConsularDistrict")
     */
    private $consularDistrict;

    public function getId(): int
    {
        return $this->id;
    }

    public function getConsularDistricts(): ConsularDistrict
    {
        return $this->consularDistrict;
    }

    public function setConsularDistrict(ConsularDistrict $consularDistrict): void
    {
        $this->consularDistrict = $consularDistrict;
    }
}

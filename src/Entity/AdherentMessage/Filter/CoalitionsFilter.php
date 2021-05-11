<?php

namespace App\Entity\AdherentMessage\Filter;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Coalition\Cause;
use App\Validator\ValidMessageCoalitionsFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @ValidMessageCoalitionsFilter
 *
 * @ApiResource(
 *     itemOperations={},
 *     collectionOperations={},
 *     subresourceOperations={},
 * )
 */
class CoalitionsFilter extends AbstractUserFilter
{
    /**
     * @var Cause|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Coalition\Cause")
     *
     * @Assert\NotBlank
     *
     * @Groups({"adherent_message_update_filter"})
     */
    private $cause;

    public function __construct(Cause $cause = null)
    {
        $this->cause = $cause;
    }

    public function getCause(): ?Cause
    {
        return $this->cause;
    }

    public function setCause(?Cause $cause): void
    {
        $this->cause = $cause;
    }
}

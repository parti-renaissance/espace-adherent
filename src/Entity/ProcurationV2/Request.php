<?php

namespace App\Entity\ProcurationV2;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="procuration_v2_requests")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\RequestRepository")
 */
class Request extends AbstractProcuration
{
    /**
     * @Assert\Valid
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationV2\Proxy", inversedBy="requests")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Proxy $proxy = null;

    public function setProxy(?Proxy $proxy): void
    {
        $proxy->addRequest($this);
    }
}

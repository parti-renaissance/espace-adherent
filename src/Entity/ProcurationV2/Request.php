<?php

namespace App\Entity\ProcurationV2;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="procuration_v2_requests")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\RequestRepository")
 */
class Request extends AbstractProcuration
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationV2\Proxy", inversedBy="requests")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Proxy $proxy = null;
}

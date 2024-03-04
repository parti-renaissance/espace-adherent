<?php

namespace App\Entity\Procuration;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="procuration_v2_proxies")
 * @ORM\Entity(repositoryClass="App\Repository\Procuration\ProxyRepository")
 */
class Proxy extends AbstractProcuration
{
    /**
     * @ORM\Column(length=9)
     */
    public string $electorNumber;

    /**
     * @ORM\Column(type="smallint", options={"default": 1, "unsigned": true})
     */
    public string $slots;
}

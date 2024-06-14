<?php

namespace App\Entity\ProcurationV2;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'procuration_v2_proxy_slot')]
#[ORM\Entity]
class ProxySlot extends AbstractSlot
{
    #[ORM\ManyToOne(inversedBy: 'proxySlots', targetEntity: Proxy::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public Proxy $proxy;

    #[ORM\OneToOne(mappedBy: 'proxySlot', targetEntity: RequestSlot::class)]
    public ?RequestSlot $requestSlot = null;

    public function __construct(Round $round, Proxy $proxy)
    {
        parent::__construct($round);

        $this->proxy = $proxy;
    }

    #[Groups(['procuration_matched_proxy', 'procuration_proxy_list'])]
    public function getRequest(): ?Request
    {
        return $this->requestSlot?->request;
    }
}

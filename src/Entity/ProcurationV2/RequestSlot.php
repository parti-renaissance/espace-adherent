<?php

namespace App\Entity\ProcurationV2;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Table(name: 'procuration_v2_request_slot')]
#[ORM\Entity]
class RequestSlot extends AbstractSlot
{
    #[ORM\ManyToOne(inversedBy: 'requestSlots', targetEntity: Request::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    public Request $request;

    #[ORM\OneToOne(inversedBy: 'requestSlot', targetEntity: ProxySlot::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    public ?ProxySlot $proxySlot = null;

    public function __construct(Round $round, Request $request)
    {
        parent::__construct($round);

        $this->request = $request;
    }

    #[Groups(['procuration_request_read', 'procuration_request_list'])]
    public function getProxy(): ?Proxy
    {
        return $this->proxySlot?->proxy;
    }
}

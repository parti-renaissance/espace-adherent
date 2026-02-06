<?php

declare(strict_types=1);

namespace App\Entity\Procuration;

use App\Procuration\ProcurationActionStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'procuration_proxy_action')]
class ProxyAction extends AbstractProcurationAction
{
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Proxy::class, inversedBy: 'actions')]
    public Proxy $proxy;

    public function __construct(UuidInterface $uuid, \DateTimeInterface $date, ProcurationActionStatusEnum $status, Proxy $proxy)
    {
        parent::__construct($uuid, $date, $status);

        $this->proxy = $proxy;
    }

    private static function create(ProcurationActionStatusEnum $status, Proxy $proxy): self
    {
        return new self(
            Uuid::uuid4(),
            new \DateTime(),
            $status,
            $proxy
        );
    }

    public static function createStatusUpdate(Proxy $proxy): self
    {
        return self::create(ProcurationActionStatusEnum::STATUS_UPDATE, $proxy);
    }
}

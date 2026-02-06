<?php

declare(strict_types=1);

namespace App\Entity\Procuration;

use App\Procuration\ProcurationActionStatusEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'procuration_request_action')]
class RequestAction extends AbstractProcurationAction
{
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Request::class, inversedBy: 'actions')]
    public Request $request;

    public function __construct(UuidInterface $uuid, \DateTimeInterface $date, ProcurationActionStatusEnum $status, Request $request)
    {
        parent::__construct($uuid, $date, $status);

        $this->request = $request;
    }

    public static function create(ProcurationActionStatusEnum $status, Request $request): self
    {
        return new self(
            Uuid::uuid4(),
            new \DateTime(),
            $status,
            $request
        );
    }
}

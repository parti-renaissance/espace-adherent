<?php

declare(strict_types=1);

namespace App\Entity\BesoinDEurope;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\EntityUTMTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'besoindeurope_inscription_requests')]
class InscriptionRequest
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[ORM\Column]
    public ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    public ?string $clientIp = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function createForEmail(string $email): self
    {
        $object = new self();

        $object->email = $email;

        return $object;
    }
}

<?php

declare(strict_types=1);

namespace App\Scope;

use App\Entity\Adherent;
use Symfony\Component\Serializer\Attribute\Groups;

class DelegatedAccess
{
    #[Groups(['scope'])]
    private Adherent $delegator;

    #[Groups(['scope'])]
    private string $role;

    #[Groups(['scope'])]
    private string $type;

    public function __construct(Adherent $delegator, string $type, string $role)
    {
        $this->delegator = $delegator;
        $this->type = $type;
        $this->role = $role;
    }

    public function getDelegator(): Adherent
    {
        return $this->delegator;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}

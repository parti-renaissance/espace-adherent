<?php

declare(strict_types=1);

namespace App\Adhesion\Command;

use App\Adhesion\Request\MembershipRequest;
use App\Entity\Adherent;

class CreateAccountCommand
{
    public function __construct(
        public readonly MembershipRequest $membershipRequest,
        public readonly ?Adherent $currentUser = null,
    ) {
    }
}

<?php

namespace App\Adhesion\Command;

use App\Adhesion\MembershipRequest;
use App\Entity\Adherent;

class CreateAccountCommand
{
    public function __construct(
        public readonly MembershipRequest $membershipRequest,
        public readonly ?Adherent $currentUser = null,
    ) {
    }
}

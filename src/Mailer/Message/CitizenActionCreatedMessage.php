<?php

namespace AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use Ramsey\Uuid\Uuid;

class CitizenActionCreatedMessage extends Message
{
    public function create(CitizenProject $citizenProject): self
    {
        return new self(
            Uuid::uuid4(),
            '326404'

        );
    }
}

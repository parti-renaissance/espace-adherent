<?php

declare(strict_types=1);

namespace App\Poll\Request;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class CreatePollVoteRequest
{
    #[Assert\NotBlank]
    public ?Uuid $choice = null;
}

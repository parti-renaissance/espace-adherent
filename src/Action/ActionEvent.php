<?php

declare(strict_types=1);

namespace App\Action;

use App\Entity\Action\Action;
use App\Entity\Adherent;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    public function __construct(
        private readonly ?Adherent $author,
        private readonly Action $action,
    ) {
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getAction(): Action
    {
        return $this->action;
    }
}

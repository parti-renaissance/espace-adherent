<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class CommitteeFeedMessage extends CommitteeFeedItem
{
    private $type = self::MESSAGE;

    /**
     * @ORM\Column(type="text", length=1500)
     */
    protected $content;

    public static function create(string $message, Committee $committee, Adherent $adherent): self
    {
        $self = new self($committee, $adherent);
        $self->content = $message;

        return $self;
    }

    public function getContent()
    {
        return $this->content;
    }
}

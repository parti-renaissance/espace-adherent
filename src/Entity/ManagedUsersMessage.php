<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class ManagedUsersMessage
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(name="adherent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $from;

    /**
     * @ORM\Column
     */
    protected $subject;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\Column(type="bigint")
     */
    protected $offset;

    public function incrementOffset(int $offset): void
    {
        $this->offset += $offset;
    }

    public function getFrom(): Adherent
    {
        return $this->from;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }
}

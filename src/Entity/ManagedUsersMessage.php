<?php

namespace AppBundle\Entity;

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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
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
    protected $offsetCount;

    public function incrementOffset(int $offset): void
    {
        $this->offsetCount += $offset;
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
        return $this->offsetCount;
    }
}

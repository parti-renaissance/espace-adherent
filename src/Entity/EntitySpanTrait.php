<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

trait EntitySpanTrait
{
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $onGoing = false;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date")
     *
     * @Assert\LessThanOrEqual("today")
     */
    private $startedAt;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $endedAt;

    public function isOnGoing(): bool
    {
        return $this->onGoing;
    }

    public function setOnGoing(bool $onGoing): void
    {
        $this->onGoing = $onGoing;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        if ($this->startedAt instanceof \DateTime) {
            $this->startedAt = \DateTimeImmutable::createFromMutable($this->startedAt);
        }

        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        if ($this->endedAt instanceof \DateTime) {
            $this->endedAt = \DateTimeImmutable::createFromMutable($this->endedAt);
        }

        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt): void
    {
        $this->endedAt = $endedAt;
    }

    public function getLength(): string
    {
        $length = '';
        $period = $this->startedAt->diff($this->endedAt ?: new \DateTime());

        if ($period->y) {
            $length .= $period->y.' an'.($period->y > 1 ? 's' : '');
        }

        if ($period->m) {
            $length .= ($period->y ? ' et ' : '').$period->m.' mois';
        }

        return ($length ?: '1 mois').($this->onGoing ? ', en cours' : '');
    }

    /**
     * @Assert\IsTrue(message="summary.spanable_item.length.invalid")
     */
    public function hasValidDuration(): bool
    {
        return !$this->onGoing && $this->endedAt || $this->onGoing && (!$this->endedAt || $this->endedAt > new \DateTime());
    }
}

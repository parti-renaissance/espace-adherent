<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="note_consultation")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Consultation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * Response time in seconds
     *
     * @var int
     *
     * @Assert\GreaterThanOrEqual(0)
     *
     * @ORM\Column(type="smallint", options={"unsigned": true})
     */
    private $responseTime;

    /**
     * @ORM\Column(type="date")
     */
    private $startedAt;

    /**
     * @ORM\Column(type="date")
     */
    private $endedAt;

    /**
     * @Assert\Url
     *
     * @ORM\Column
     */
    private $url;

    /**
     * @ORM\Column
     */
    private $name;

    public function __construct(
        string $name,
        string $url,
        \DateTime $startedAt,
        \DateTime $endedAt,
        $responseTime = 0
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->startedAt = $startedAt;
        $this->endedAt = $endedAt;
        $this->responseTime = $responseTime;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResponseTime(): int
    {
        return $this->responseTime;
    }

    public function setResponseTime(int $responseTime): void
    {
        $this->responseTime = $responseTime;
    }

    public function getStartedAt(): \DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTime $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getEndedAt(): \DateTime
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTime $endedAt): void
    {
        $this->endedAt = $endedAt;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CertificationRequestRepository")
 * @ORM\Table(name="certification_request")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CertificationRequest
{
    use EntityTimestampableTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REFUSED = 'refused';

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20)
     */
    private $status;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $annotations;

    /**
     * @var Adherent
     *
     * @ORM\OneToOne(targetEntity=Adherent::class, mappedBy="certificationRequest")
     */
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
        $this->status = self::STATUS_PENDING;
    }

    public function __toString()
    {
        return sprintf('%s (%s)', $this->adherent, $this->status);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getAnnotations(): ?array
    {
        return $this->annotations;
    }

    public function setAnnotations(?array $annotations): void
    {
        $this->annotations = $annotations;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function isPending(): bool
    {
        return self::STATUS_PENDING === $this->status;
    }

    public function isApproved(): bool
    {
        return self::STATUS_APPROVED === $this->status;
    }

    public function isRefused(): bool
    {
        return self::STATUS_REFUSED === $this->status;
    }

    public function approve(): void
    {
        $this->status = self::STATUS_APPROVED;
    }

    public function refuse(): void
    {
        $this->status = self::STATUS_REFUSED;
    }
}

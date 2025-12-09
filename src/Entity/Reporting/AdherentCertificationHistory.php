<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use App\Entity\Administrator;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['adherent_id'], name: 'adherent_certification_histories_adherent_id_idx')]
#[ORM\Index(columns: ['administrator_id'], name: 'adherent_certification_histories_administrator_id_idx')]
#[ORM\Index(columns: ['date'], name: 'adherent_certification_histories_date_idx')]
#[ORM\Table(name: 'adherent_certification_histories')]
class AdherentCertificationHistory
{
    private const ACTION_CERTIFY = 'certify';
    private const ACTION_UNCERTIFY = 'uncertify';

    public const ACTION_CHOICES = [
        self::ACTION_CERTIFY,
        self::ACTION_UNCERTIFY,
    ];

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var Adherent
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var Administrator|null
     */
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    private $administrator;

    /**
     * @var string
     */
    #[ORM\Column(length: 20)]
    private $action;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $date;

    private function __construct(Adherent $adherent, ?Administrator $administrator, string $action)
    {
        $this->adherent = $adherent;
        $this->administrator = $administrator;
        $this->action = $action;
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function isCertify(): bool
    {
        return self::ACTION_CERTIFY === $this->action;
    }

    public function isUncertify(): bool
    {
        return self::ACTION_UNCERTIFY === $this->action;
    }

    public static function createCertify(Adherent $adherent, ?Administrator $administrator = null): self
    {
        return new self($adherent, $administrator, self::ACTION_CERTIFY);
    }

    public static function createUncertify(Adherent $adherent, Administrator $administrator): self
    {
        return new self($adherent, $administrator, self::ACTION_UNCERTIFY);
    }
}

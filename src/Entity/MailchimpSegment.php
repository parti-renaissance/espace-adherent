<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MailchimpSegmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MailchimpSegmentRepository::class)]
class MailchimpSegment implements \Stringable
{
    public const LIST_MAIN = 'main';
    public const LIST_ELECTED_REPRESENTATIVE = 'elected_representative';

    public const LISTS = [
        self::LIST_MAIN,
        self::LIST_ELECTED_REPRESENTATIVE,
    ];

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column]
    private $list;

    /**
     * @var string
     */
    #[ORM\Column]
    private $label;

    #[ORM\Column(nullable: true)]
    private ?int $externalId;

    public function __construct(string $list, string $label, ?int $externalId = null)
    {
        $this->list = $list;
        $this->label = $label;
        $this->externalId = $externalId;
    }

    public static function createElectedRepresentativeSegment(string $label, ?string $externalId = null): MailchimpSegment
    {
        return new self(self::LIST_ELECTED_REPRESENTATIVE, $label, $externalId);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getList(): string
    {
        return $this->list;
    }

    public function setList(string $list): void
    {
        $this->list = $list;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(?int $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function __toString()
    {
        return \sprintf('[%s] %s', $this->list, $this->label);
    }
}

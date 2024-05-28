<?php

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Procuration\V2\MatchingHistoryActionEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'procuration_v2_matching_history')]
#[ORM\Entity]
class MatchingHistory
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    #[ORM\Column(enumType: MatchingHistoryActionEnum::class)]
    public MatchingHistoryActionEnum $status;

    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    public bool $emailCopy;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Request::class, inversedBy: 'matchingHistories')]
    public Request $request;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Proxy::class)]
    public Proxy $proxy;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $matcher = null;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    public ?Administrator $adminMatcher = null;

    public function __construct(
        MatchingHistoryActionEnum $status,
        Request $request,
        Proxy $proxy,
        bool $emailCopy
    ) {
        $this->status = $status;
        $this->request = $request;
        $this->proxy = $proxy;
        $this->emailCopy = $emailCopy;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function createMatch(Request $request, Proxy $proxy, bool $emailCopy): self
    {
        return new self(MatchingHistoryActionEnum::MATCH, $request, $proxy, $emailCopy);
    }

    public static function createUnmatch(Request $request, Proxy $proxy, bool $emailCopy): self
    {
        return new self(MatchingHistoryActionEnum::UNMATCH, $request, $proxy, $emailCopy);
    }
}

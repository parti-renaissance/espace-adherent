<?php

declare(strict_types=1);

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Procuration\V2\MatchingHistoryActionEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'procuration_v2_matching_history')]
class MatchingHistory
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
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

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Round::class)]
    public Round $round;

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
        Round $round,
        bool $emailCopy,
    ) {
        $this->status = $status;
        $this->request = $request;
        $this->proxy = $proxy;
        $this->round = $round;
        $this->emailCopy = $emailCopy;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function createMatch(Request $request, Proxy $proxy, Round $round, bool $emailCopy): self
    {
        return new self(MatchingHistoryActionEnum::MATCH, $request, $proxy, $round, $emailCopy);
    }

    public static function createUnmatch(Request $request, Proxy $proxy, Round $round, bool $emailCopy): self
    {
        return new self(MatchingHistoryActionEnum::UNMATCH, $request, $proxy, $round, $emailCopy);
    }
}

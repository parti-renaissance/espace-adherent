<?php

namespace App\Entity\ProcurationV2;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Procuration\V2\MatchingHistoryActionEnum;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="procuration_v2_matching_history")
 * @ORM\Entity
 */
class MatchingHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(enumType=MatchingHistoryActionEnum::class)
     */
    public MatchingHistoryActionEnum $status;

    /**
     * @ORM\Column(type="datetime")
     */
    public \DateTimeInterface $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationV2\Request", inversedBy="matchingHistories")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Request $request;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ProcurationV2\Proxy")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Proxy $proxy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Adherent $matcher = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Administrator $adminMatcher = null;

    public function __construct(
        MatchingHistoryActionEnum $status,
        Request $request,
        Proxy $proxy
    ) {
        $this->status = $status;
        $this->request = $request;
        $this->proxy = $proxy;
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function createMatch(Request $request, Proxy $proxy): self
    {
        return new self(MatchingHistoryActionEnum::MATCH, $request, $proxy);
    }

    public static function createUnmatch(Request $request, Proxy $proxy): self
    {
        return new self(MatchingHistoryActionEnum::UNMATCH, $request, $proxy);
    }
}

<?php

namespace App\Entity\Chatbot;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="chatbot_thread")
 */
class Thread
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use OpenAIResourceTrait;

    public const STATUS_RUN_IN_PROGRESS = 'in_progress';
    public const STATUS_RUN_COMPLETED = 'completed';

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Adherent $adherent = null;

    /**
     * @ORM\OneToMany(
     *     targetEntity=Message::class,
     *     mappedBy="thread",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    public Collection $messages;

    /**
     * @ORM\OneToOne(targetEntity=Run::class, cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Run $currentRun = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->messages = new ArrayCollection();
    }
}

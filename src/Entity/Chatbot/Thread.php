<?php

declare(strict_types=1);

namespace App\Entity\Chatbot;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(),
    ],
    routePrefix: '/v3/ai',
    normalizationContext: ['groups' => ['chatbot:thread_read']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'chatbot')"
)]
#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ORM\Table(name: 'chatbot_thread')]
class Thread
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[Groups(['chatbot:thread_read'])]
    #[ORM\Column(nullable: true)]
    public ?string $title = null;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    /**
     * @var Message[]|Collection
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'thread', cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'ASC'])]
    public Collection $messages;

    public function __construct(Adherent $adherent, ?string $title = null, ?Uuid $uuid = null)
    {
        $this->adherent = $adherent;
        $this->title = $title;
        $this->uuid = $uuid ?? Uuid::v4();
        $this->messages = new ArrayCollection();
    }

    public function addAssistantMessage(string $content, \DateTimeInterface $date): Message
    {
        return $this->addMessage(Message::ROLE_ASSISTANT, $content, $date);
    }

    public function addUserMessage(string $content, ?\DateTimeInterface $date = null): Message
    {
        return $this->addMessage(Message::ROLE_USER, $content, $date);
    }

    private function addMessage(string $role, string $content, ?\DateTimeInterface $date = null): Message
    {
        $message = new Message();
        $message->thread = $this;
        $message->role = $role;
        $message->content = $content;
        $message->date = $date ?? new \DateTime('now');

        $this->messages->add($message);

        return $message;
    }
}

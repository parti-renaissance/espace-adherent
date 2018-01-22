<?php

namespace AppBundle\Entity\WebHook;

use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\WebHook\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WebHookRepository")
 * @ORM\Table(name="web_hooks", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="web_hook_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="web_hook_event_unique", columns="event")
 * })
 */
class WebHook
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=64)
     *
     * @JMS\Groups({"api"})
     */
    private $event;

    /**
     * @var Collection|Callback[]
     *
     * @ORM\OneToMany(targetEntity="Callback", mappedBy="webHook", cascade={"all"}, orphanRemoval=true)
     *
     * @Assert\Valid
     */
    private $callbacks;

    public function __construct(Event $event, $callbacks = [])
    {
        $this->uuid = Uuid::uuid4();
        $this->event = $event->getValue();
        $this->callbacks = new ArrayCollection();

        foreach ($callbacks as $callback) {
            $this->addCallback($callback);
        }
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"api"})
     * @JMS\SerializedName("callbacks")
     */
    public function getCallbackUrls(): array
    {
        $urls = [];

        foreach ($this->getCallbacks() as $callback) {
            $urls[] = $callback->getUrls();
        }

        return $urls ? array_merge(...$urls) : [];
    }

    /**
     * @return Callback[]|Collection
     */
    public function getCallbacks(): Collection
    {
        return $this->callbacks;
    }

    public function removeCallback(Callback $callback): void
    {
        if ($this->callbacks->contains($callback)) {
            $this->callbacks->remove($callback);
        }
    }

    public function addCallback(Callback $callback): void
    {
        if (!$this->callbacks->contains($callback)) {
            $callback->setWebHook($this);
            $this->callbacks->add($callback);
        }
    }

    public function __toString()
    {
        return $this->getEvent();
    }
}

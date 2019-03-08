<?php

namespace AppBundle\Entity\WebHook;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\OAuth\Client;
use AppBundle\WebHook\Event;
use AppBundle\WebHook\Service;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WebHookRepository")
 * @ORM\Table(name="web_hooks", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="web_hook_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="web_hook_event_client_id_unique", columns={"event", "client_id"})
 * })
 * @UniqueEntity(fields={"event", "client"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class WebHook
{
    use EntityIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(length=64)
     *
     * @Assert\NotBlank
     */
    private $event;

    /**
     * This property is needed to manage to have the unique constraint "web_hook_event_client_id_unique" with Doctrine ORM.
     *
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OAuth\Client", inversedBy="webHooks")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull
     */
    private $client;

    /**
     * @var string
     *
     * @ORM\Column(length=64, nullable=true)
     */
    private $service;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     *
     * @Assert\Count(min=1, minMessage="Veuillez spécifier au moins une url de callback.")
     */
    private $callbacks = [];

    public function __construct(
        Client $client = null,
        Event $event = null,
        array $callbacks = [],
        Service $service = null
    ) {
        $this->uuid = Uuid::uuid4();
        $this->client = $client;
        $this->event = $event ? $event->getValue() : null;
        $this->service = $service ? $service->getValue() : null;

        foreach ($callbacks as $callback) {
            $this->addCallback($callback);
        }
    }

    public function getEvent(): ?string
    {
        return $this->event;
    }

    public function setEvent(string $event): void
    {
        if (!Event::isValid($event)) {
            throw new \DomainException("$event is not valid");
        }

        $this->event = $event;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): void
    {
        if ($service && !Service::isValid($service)) {
            throw new \DomainException("$service is not valid");
        }

        $this->service = $service;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function addCallback(string $callback): void
    {
        if (!\in_array($callback, $this->callbacks, true)) {
            $this->callbacks[] = $callback;
        }
    }

    public function removeCallback(string $callback): void
    {
        if (false !== ($key = array_search($callback, $this->callbacks, true))) {
            unset($this->callbacks[$key]);
        }
    }

    public function getCallbacks(): array
    {
        return array_values($this->callbacks);
    }
}

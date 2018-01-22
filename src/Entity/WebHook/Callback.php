<?php

namespace AppBundle\Entity\WebHook;

use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\OAuth\Client;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="web_hook_callbacks", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="web_hook_callback_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="web_hook_callback_vs_client_unique", columns={"web_hook_id", "client_id"})
 * })
 * @UniqueEntity(fields={"webHook", "client"})
 */
class Callback
{
    use EntityIdentityTrait;

    /**
     * This field is only required in order to apply the unique constraint between a Client and a WebHook.
     *
     * @var WebHook
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\WebHook\WebHook", inversedBy="callbacks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $webHook;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OAuth\Client")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @var string[]
     *
     * @ORM\Column(type="json")
     *
     * @Assert\Count(min="1")
     */
    private $urls;

    public function __construct(Client $client = null, array $urls = [])
    {
        $this->uuid = Uuid::uuid4();
        $this->client = $client;

        $this->urls = [];
        foreach ($urls as $url) {
            $this->addUrl($url);
        }
    }

    public function setWebHook(WebHook $webHook): void
    {
        $this->webHook = $webHook;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function addUrl(string $url): void
    {
        if (!in_array($url, $this->urls, true)) {
            $this->urls[] = $url;
        }
    }

    public function removeUrl(string $url): void
    {
        if (false !== ($key = array_search($url, $this->urls, true))) {
            unset($this->urls[$key]);
        }
    }

    public function getUrls(): array
    {
        return array_values($this->urls);
    }
}

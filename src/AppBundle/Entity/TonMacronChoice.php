<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="ton_macron_choices", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="ton_macron_choices_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="ton_macron_choices_key_unique", columns="key")
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TonMacronChoiceRepository")
 */
final class TonMacronChoice
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    /**
     * @ORM\Column(type="smallint", length=1, options={"unsigned": true})
     */
    private $step;

    /**
     * @ORM\Column(length=10)
     */
    private $key;

    /**
     * @ORM\Column(length=100)
     */
    private $label;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    public function __construct(UuidInterface $uuid, string $step, string $key, string $label, string $content)
    {
        $this->uuid = $uuid;
        $this->step = $step;
        $this->key = $key;
        $this->label = $label;
        $this->content = $content;
    }

    public function getStep(): string
    {
        return $this->step;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}

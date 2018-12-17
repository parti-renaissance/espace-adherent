<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityIdentityTrait
{
    /**
     * The unique auto incremented primary key.
     *
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * The internal primary identity key.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @Algolia\Attribute
     *
     * @SymfonySerializer\Groups({"idea_list_read", "thread_comment_read"})
     */
    protected $uuid;

    /**
     * Returns the primary key identifier.
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the internal unique UUID instance.
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}

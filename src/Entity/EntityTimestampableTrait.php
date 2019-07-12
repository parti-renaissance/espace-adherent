<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait EntityTimestampableTrait
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @SymfonySerializer\Groups({"idea_list_read", "thread_list_read", "thread_comment_read", "idea_read"})
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @Algolia\Attribute(algoliaName="created_at")
     */
    public function getReadableCreatedAt(): string
    {
        return $this->createdAt->format('d/m/Y H:i');
    }

    /**
     * @Algolia\Attribute(algoliaName="updated_at")
     */
    public function getReadableUpdatedAt(): string
    {
        return $this->updatedAt->format('d/m/Y H:i');
    }
}

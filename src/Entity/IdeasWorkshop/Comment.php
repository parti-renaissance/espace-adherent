<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use AppBundle\Entity\EntitySoftDeletableTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="note_comment")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Comment
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Thread", inversedBy="comments")
     */
    private $thread;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $adherent;

    public static function create(
        UuidInterface $uuid,
        string $text
    ): Comment {
        $comment = new self();

        $comment->uuid = $uuid;
        $comment->text = $text;

        return $comment;
    }

    public function getThread(): Thread
    {
        return $this->thread;
    }

    public function setThread($thread): void
    {
        $this->thread = $thread;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent($adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText($text): void
    {
        $this->text = $text;
    }
}

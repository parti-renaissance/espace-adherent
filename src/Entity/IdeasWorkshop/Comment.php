<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntitySoftDeletableTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="iw_comment")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Comment
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

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

    public function __construct(
        string $text,
        Adherent $adherent,
        Thread $thread
    ) {
        $this->text = $text;
        $this->adherent = $adherent;
        $this->thread = $thread;
    }

    public function getId(): ?int
    {
        return $this->id;
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

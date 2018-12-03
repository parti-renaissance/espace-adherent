<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntitySoftDeletableTrait;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="ideas_workshop_comment")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class ThreadComment
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
    private $content;

    /**
     * @JMS\Groups({"idea_list"})
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $adherent;

    public function __construct(
        string $content,
        Adherent $adherent,
        Thread $thread
    ) {
        $this->content = $content;
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

    public function setThread(Thread $thread): void
    {
        $this->thread = $thread;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}

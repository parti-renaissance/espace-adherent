<?php

namespace AppBundle\Entity\IdeasWorkshop;

use AppBundle\Entity\Adherent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * @ORM\Table(name="note_answer")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Answer
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $adherent;

    /**
     * @ORM\OneToOne(targetEntity="Question", inversedBy="answer")
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="answer")
     */
    private $threads;

    public function __construct()
    {
        $this->threads = new ArrayCollection();
    }

    public static function create(
        string $text
    ): Answer {
        $answer = new self();

        $answer->text = $text;

        return $answer;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText($text): void
    {
        $this->text = $text;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setAdherent($adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion($question): void
    {
        $this->question = $question;
    }

    public function addThread(Thread $thread): void
    {
        if (!$this->threads->contains($thread)) {
            $this->threads->add($thread);
        }
    }

    public function removeThread(Thread $thread): void
    {
        $this->threads->removeElement($thread);
    }

    public function getThreads(): ArrayCollection
    {
        return $this->threads;
    }
}

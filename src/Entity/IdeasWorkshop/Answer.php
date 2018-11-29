<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * @ORM\Table(name="ideas_workshop_answer")
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
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="answers")
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="answer")
     */
    private $threads;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="answers")
     */
    private $idea;

    public function __construct(string $text)
    {
        $this->text = $text;
        $this->threads = new ArrayCollection();
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

    public function getIdea(): Idea
    {
        return $this->idea;
    }

    public function setIdea($idea): void
    {
        $this->idea = $idea;
    }
}

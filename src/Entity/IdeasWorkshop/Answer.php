<?php

namespace AppBundle\Entity\IdeasWorkshop;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ApiResource(
 *     collectionOperations={
 *         "get",
 *         "post": {"access_control": "is_granted('ROLE_ADHERENT')"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put": {"access_control": "object.getAuthor() == user"}
 *     }
 * )
 *
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
     *
     * @SymfonySerializer\Groups("thread_comment_read")
     */
    private $id;

    /**
     * @SymfonySerializer\Groups("idea_write")
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="Thread", mappedBy="answer", cascade={"remove"}, orphanRemoval=true)
     */
    private $threads;

    /**
     * @ORM\ManyToOne(targetEntity="Idea", inversedBy="answers")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $idea;

    public function __construct(string $content)
    {
        $this->content = $content;
        $this->threads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    public function addThread(Thread $thread): void
    {
        if (!$this->threads->contains($thread)) {
            $this->threads->add($thread);
            $thread->setAnswer($this);
        }
    }

    public function removeThread(Thread $thread): void
    {
        $this->threads->removeElement($thread);
    }

    public function getThreads(): Collection
    {
        return $this->threads;
    }

    public function getIdea(): ?Idea
    {
        return $this->idea;
    }

    public function setIdea(Idea $idea): void
    {
        $this->idea = $idea;
    }
}

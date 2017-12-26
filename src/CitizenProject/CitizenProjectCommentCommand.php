<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenProjectCommentCommand
{
    /**
     * @var CitizenProject
     *
     * @Assert\NotNull
     */
    private $citizenProject;

    /**
     * @var Adherent Any member of the citizen project
     */
    private $author;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $content;

    public function __construct(CitizenProject $citizenProject, ?Adherent $author)
    {
        $this->citizenProject = $citizenProject;
        $this->author = $author;
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}

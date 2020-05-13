<?php

namespace App\Entity\Jecoute;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Jecoute\LocalSurveyRepository")
 */
class LocalSurvey extends Survey implements AuthoredInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Length(max=255)
     *
     * @JMS\Groups({"survey_list"})
     */
    private $city;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $tags = [];

    public function __construct(
        ?Adherent $author = null,
        ?string $name = null,
        ?string $city = null,
        ?bool $published = false
    ) {
        $this->author = $author;
        $this->city = $city;

        parent::__construct($name, $published);
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function setAuthor(Adherent $author = null): void
    {
        $this->author = $author;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getType(): string
    {
        return SurveyTypeEnum::LOCAL;
    }
}

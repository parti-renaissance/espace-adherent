<?php

namespace AppBundle\Entity\Jecoute;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthoredInterface;
use AppBundle\Jecoute\SurveyTypeEnum;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Jecoute\LocalSurveyRepository")
 */
class LocalSurvey extends Survey implements AuthoredInterface
{
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
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

    public function __construct(Adherent $author, string $name = null, string $city = null, bool $published = false)
    {
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

    public function getType(): string
    {
        return SurveyTypeEnum::LOCAL;
    }
}

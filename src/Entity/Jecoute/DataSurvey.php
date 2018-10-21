<?php

namespace AppBundle\Entity\Jecoute;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\Adherent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="jecoute_data_survey")
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class DataSurvey
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $postedAt;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(nullable=true)
     *
     * @Assert\Email
     * @Assert\Length(max=255, maxMessage="common.email.max_length")
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="boolean")
     */
    private $agreedToStayInContact = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $agreedToJoinParisOperation = false;

    /**
     * @var DataAnswer[]|Collection
     *
     * @ORM\OneToMany(targetEntity="DataAnswer", mappedBy="dataSurvey", cascade={"persist", "remove"})
     *
     * @Assert\Valid
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    private $survey;

    public function __construct(Survey $survey = null, string $firstName = null, string $lastName = null)
    {
        $this->survey = $survey;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAuthor(Adherent $author): void
    {
        $this->author = $author;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getPostedAt(): ?\DateTime
    {
        return $this->postedAt;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
    }

    public function getAgreedToStayInContact(): bool
    {
        return $this->agreedToStayInContact;
    }

    public function setAgreedToStayInContact(bool $agreedToStayInContact): void
    {
        $this->agreedToStayInContact = $agreedToStayInContact;
    }

    public function getAgreedToJoinParisOperation(): bool
    {
        return $this->agreedToJoinParisOperation;
    }

    public function setAgreedToJoinParisOperation(bool $agreedToJoinParisOperation): void
    {
        $this->agreedToJoinParisOperation = $agreedToJoinParisOperation;
    }

    public function addAnswer(DataAnswer $answer): void
    {
        if (!$this->answers->contains($answer)) {
            $answer->setDataSurvey($this);
            $this->answers->add($answer);
        }
    }

    public function removeAnswer(DataAnswer $answer): void
    {
        $this->answers->removeElement($answer);
    }

    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }
}

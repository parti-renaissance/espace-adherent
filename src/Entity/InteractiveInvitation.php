<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InteractiveInvitationRepository")
 * @ORM\Table(name="interactive_invitations", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="interactive_invitations_uuid_unique", columns="uuid")
 * })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "my_europe": "AppBundle\Entity\MyEuropeInvitation",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class InteractiveInvitation
{
    use EntityIdentityTrait;
    use EntityCrudTrait;

    protected $friendFirstName;

    /**
     * @ORM\Column(type="smallint", length=3, options={"unsigned": true})
     */
    protected $friendAge;

    /**
     * @ORM\Column(length=6)
     */
    protected $friendGender;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    protected $friendPosition;

    /**
     * Property value is still used to send a mail, but it should not be stored in the base.
     */
    protected $friendEmailAddress;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    protected $authorFirstName;

    /**
     * @ORM\Column(length=50, nullable=true)
     */
    protected $authorLastName;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $authorEmailAddress;

    /**
     * @ORM\Column(length=100, nullable=true)
     */
    protected $mailSubject;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $mailBody;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\InteractiveChoice", fetch="EAGER")
     * @ORM\JoinTable(
     *     name="interactive_invitation_has_choices",
     *     joinColumns={
     *         @ORM\JoinColumn(name="invitation_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="choice_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $choices;

    public function __construct(
        UuidInterface $uuid,
        string $friendFirstName,
        string $friendAge,
        string $friendGender,
        string $createdAt = 'now'
    ) {
        $this->uuid = $uuid;
        $this->friendFirstName = $friendFirstName;
        $this->friendAge = $friendAge;
        $this->friendGender = $friendGender;
        $this->choices = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable($createdAt);
    }

    public function __toString(): string
    {
        return 'Invitation de '.$this->authorEmailAddress.' à '.$this->friendEmailAddress;
    }

    public function getFriendFirstName(): ?string
    {
        return $this->friendFirstName;
    }

    public function getFriendAge(): int
    {
        return $this->friendAge;
    }

    public function getFriendGender(): string
    {
        return $this->friendGender;
    }

    public function getFriendPosition(): ?string
    {
        return $this->friendPosition;
    }

    public function getFriendEmailAddress(): ?string
    {
        return $this->friendEmailAddress;
    }

    public function getAuthorFirstName(): ?string
    {
        return $this->authorFirstName;
    }

    public function getAuthorLastName(): ?string
    {
        return $this->authorLastName;
    }

    public function getAuthorEmailAddress(): ?string
    {
        return $this->authorEmailAddress;
    }

    public function getMailSubject(): ?string
    {
        return $this->mailSubject;
    }

    public function getMailBody(): ?string
    {
        return $this->mailBody;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        if ($this->createdAt instanceof \DateTime) {
            $this->createdAt = \DateTimeImmutable::createFromMutable($this->createdAt);
        }

        return $this->createdAt;
    }

    /**
     * @return ArrayCollection|InteractiveChoice[]|iterable
     */
    public function getChoices(): iterable
    {
        return $this->choices;
    }
}

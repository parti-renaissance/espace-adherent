<?php

namespace AppBundle\Committee\Event;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CommitteeEvent;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeEventRegistrationCommand
{
    /**
     * @var CommitteeEvent
     */
    private $event;

    /**
     * @var Adherent
     */
    private $adherent;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=50)
     */
    private $firstName;

    /**
     * @Assert\NotBlank
     * @Assert\Email
     * @Assert\Length(max=100)
     */
    private $emailAddress;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=15)
     */
    private $postalCode;
    private $newsletterSubscriber;
    private $registrationUuid;

    public function __construct(CommitteeEvent $event, Adherent $adherent = null)
    {
        $this->event = $event;
        $this->newsletterSubscriber = false;
        $this->registrationUuid = Uuid::uuid4();

        if ($adherent) {
            $this->setAdherent($adherent);
        }
    }

    private function setAdherent(Adherent $adherent)
    {
        $this->adherent = $adherent;
        $this->firstName = $adherent->getFirstName();
        $this->emailAddress = $adherent->getEmailAddress();
        $this->postalCode = $adherent->getPostalCode();
        $this->newsletterSubscriber = $adherent->hasSubscribedMainEmails();
    }

    public function getCommitteeEvent(): CommitteeEvent
    {
        return $this->event;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress(string $emailAddress)
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function isNewsletterSubscriber(): bool
    {
        return $this->newsletterSubscriber;
    }

    public function setNewsletterSubscriber(bool $newsletterSubscriber)
    {
        $this->newsletterSubscriber = $newsletterSubscriber;
    }

    public function getRegistrationUuid(): UuidInterface
    {
        return $this->registrationUuid;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function getAdherentUuid(): ?UuidInterface
    {
        return $this->adherent ? $this->adherent->getUuid() : null;
    }
}

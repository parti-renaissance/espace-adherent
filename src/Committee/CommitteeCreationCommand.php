<?php

namespace AppBundle\Committee;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Validator\UniqueCommittee as AssertUniqueCommittee;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueCommittee
 */
class CommitteeCreationCommand extends CommitteeCommand
{
    /**
     * @Assert\IsTrue(message="committee.must_accept_confidentiality_terms")
     */
    public $acceptConfidentialityTerms;

    /**
     * @Assert\IsTrue(message="committee.must_accept_contacting_terms")
     */
    public $acceptContactingTerms;

    /** @var Adherent */
    private $adherent;

    protected function __construct(Address $address = null)
    {
        parent::__construct($address);

        $this->acceptConfidentialityTerms = false;
        $this->acceptContactingTerms = false;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->adherent = $adherent;
        $dto->phone = $adherent->getPhone();

        return $dto;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }
}

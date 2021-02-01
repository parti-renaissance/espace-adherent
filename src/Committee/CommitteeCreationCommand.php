<?php

namespace App\Committee;

use App\Address\Address;
use App\Entity\Adherent;
use App\Entity\Committee;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeCreationCommand extends CommitteeCommand
{
    /**
     * @Assert\IsTrue(message="committee.must_accept_confidentiality_terms", groups={"created_by_adherent"})
     */
    public $acceptConfidentialityTerms;

    /**
     * @Assert\IsTrue(message="committee.must_accept_contacting_terms", groups={"created_by_adherent"})
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

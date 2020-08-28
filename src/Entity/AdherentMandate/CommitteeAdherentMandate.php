<?php

namespace App\Entity\AdherentMandate;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use App\Entity\Adherent;
use App\Entity\Committee;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AdherentMandate\CommitteeAdherentMandateRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeAdherentMandate extends AbstractAdherentMandate
{
    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", inversedBy="adherentMandates")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $committee;

    public function __construct(
        Adherent $adherent,
        string $gender,
        Committee $committee,
        \DateTime $beginAt,
        \DateTime $finishAt = null
    ) {
        parent::__construct($adherent, $gender, $beginAt, $finishAt);

        $this->committee = $committee;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setCommittee(Committee $committee): void
    {
        $this->committee = $committee;
    }
}

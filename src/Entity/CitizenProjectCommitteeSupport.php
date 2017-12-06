<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;

/**
 * This entity represents a link between CitizenProject and Committee.
 *
 * @ORM\Table(
 *   name="citizen_project_committee_supports"
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CitizenProjectCommitteeSupportRepository")
 * @Algolia\Index(autoIndex=false)
 */
class CitizenProjectCommitteeSupport
{
    const PENDING = 'PENDING';
    const APPROVED = 'APPROVED';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CitizenProject", inversedBy="committeeSupports", cascade={"persist"})
     */
    private $citizenProject;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Committee", fetch="EAGER", inversedBy="citizenProjectSupports", cascade={"persist"})
     */
    private $committee;

    /**
     * @ORM\Column(length=20)
     */
    private $status;

    /**
     * The timestamp when an citizenProject ask support.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $requestedAt;

    /**
     * The timestamp when an committee accept.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    public function __construct(
        CitizenProject $citizenProject,
        Committee $committee,
        string $status = 'PENDING',
        string $requestedAt = 'now',
        string $approvedAt = null
    ) {
        $this->citizenProject = $citizenProject;
        $this->committee = $committee;
        $this->status = $status;

        if ($requestedAt) {
            $requestedAt = new \DateTimeImmutable($requestedAt);
        }

        if ($approvedAt) {
            $approvedAt = new \DateTimeImmutable($approvedAt);
        }

        $this->requestedAt = $requestedAt;
        $this->approvedAt = $approvedAt;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isApproved(): bool
    {
        return self::APPROVED === $this->status;
    }

    public function approve(string $timestamp = 'now'): void
    {
        $this->status = self::APPROVED;
        $this->approvedAt = new \DateTime($timestamp);
    }

    public function getCitizenProject(): CitizenProject
    {
        return $this->citizenProject;
    }
}

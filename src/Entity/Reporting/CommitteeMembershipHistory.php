<?php

declare(strict_types=1);

namespace App\Entity\Reporting;

use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Index(columns: ['adherent_uuid'], name: 'committees_membership_histories_adherent_uuid_idx')]
#[ORM\Index(columns: ['action'], name: 'committees_membership_histories_action_idx')]
#[ORM\Index(columns: ['date'], name: 'committees_membership_histories_date_idx')]
#[ORM\Table(name: 'committees_membership_histories')]
class CommitteeMembershipHistory
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    protected $id;

    /**
     * @var Committee
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class, cascade: ['persist'])]
    private $committee;

    /**
     * Only UUID is used instead of the Entity because we cannot lose this data if one un-subscribes.
     * Otherwise stats would be broken.
     *
     * @var UuidInterface
     */
    #[ORM\Column(type: 'uuid')]
    private $adherentUuid;

    /**
     * @var string
     */
    #[ORM\Column(length: 10)]
    private $action;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST or SUPERVISOR or FOLLOWER.
     *
     * @var string
     */
    #[ORM\Column(length: 10)]
    private $privilege;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private $date;

    public function __construct(
        CommitteeMembership $committeeMembership,
        CommitteeMembershipAction $action,
        ?\DateTimeImmutable $date = null,
    ) {
        $this->adherentUuid = $committeeMembership->getAdherentUuid();
        $this->committee = $committeeMembership->getCommittee();
        $this->privilege = $committeeMembership->getPrivilege();
        $this->date = $date ?: new Chronos();
        $this->action = $action->getValue();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}

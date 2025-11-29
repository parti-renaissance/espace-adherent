<?php

declare(strict_types=1);

namespace App\Entity\Moodle;

use App\Entity\Adherent;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Moodle\MoodleUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoodleUserRepository::class)]
#[ORM\Table(name: 'moodle_user')]
class User
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public int $id;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\OneToOne]
    public Adherent $adherent;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    public int $moodleId;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserJob::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $jobs;

    public function __construct(Adherent $adherent, int $moodleId)
    {
        $this->adherent = $adherent;
        $this->moodleId = $moodleId;
        $this->jobs = new ArrayCollection();
    }

    /**
     * @return UserJob[]
     */
    public function getJobs(): array
    {
        return $this->jobs->toArray();
    }

    public function addJob(UserJob $job): void
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
        }
    }

    public function removeJob(UserJob $job): void
    {
        $this->jobs->removeElement($job);
    }
}

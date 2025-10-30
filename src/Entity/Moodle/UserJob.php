<?php

namespace App\Entity\Moodle;

use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'moodle_user_job')]
class UserJob
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public int $id;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(inversedBy: 'jobs')]
    public User $user;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    public int $moodleId;

    #[ORM\Column]
    public string $department;

    #[ORM\Column]
    public string $position;

    #[ORM\Column]
    public string $jobKey;

    public function __construct(User $user, int $moodleId, string $department, string $position, string $key)
    {
        $this->user = $user;
        $this->moodleId = $moodleId;
        $this->department = $department;
        $this->position = $position;
        $this->jobKey = $key;
    }
}

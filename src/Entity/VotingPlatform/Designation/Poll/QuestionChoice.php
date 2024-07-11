<?php

namespace App\Entity\VotingPlatform\Designation\Poll;

use App\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'designation_poll_question_choice')]
class QuestionChoice
{
    use EntityIdentityTrait;

    #[Assert\NotBlank]
    #[ORM\Column]
    public ?string $label = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PollQuestion::class, inversedBy: 'choices')]
    public ?PollQuestion $question = null;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }
}

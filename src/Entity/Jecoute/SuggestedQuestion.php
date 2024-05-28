<?php

namespace App\Entity\Jecoute;

use App\Repository\Jecoute\SuggestedQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'jecoute_suggested_question')]
#[ORM\Entity(repositoryClass: SuggestedQuestionRepository::class)]
class SuggestedQuestion extends Question
{
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $published;

    public function __construct(?string $content = null, ?string $type = null, bool $published = false)
    {
        parent::__construct($content, $type);

        $this->published = $published;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }
}

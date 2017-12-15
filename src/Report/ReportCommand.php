<?php

namespace AppBundle\Report;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Report;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReportCommand
{
    /**
     * @var Adherent
     */
    private $author;

    /**
     * @var mixed
     */
    private $subject;

    /**
     * @var string
     */
    private $subjectType;

    /**
     * @Assert\NotBlank(message="report.invalid_reasons")
     */
    private $reasons = [];

    /**
     * @Assert\Length(min=10, max=1000)
     */
    private $comment;

    public function __construct($subject, string $subjectType, Adherent $author)
    {
        $this->author = $author;
        $this->subject = $subject;
        $this->subjectType = $subjectType;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function getSubjectType(): string
    {
        return $this->subjectType;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function setReasons(array $reasons): void
    {
        $this->reasons = $reasons;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @Assert\Callback
     */
    public function validateComment(ExecutionContextInterface $context): void
    {
        if ($this->comment && !in_array(Report::REASON_OTHER, $this->reasons)) {
            $context->addViolation('Vous devez cocher la case "Autre" afin de renseigner un commentaire');
        }

        if (!$this->comment && in_array(Report::REASON_OTHER, $this->reasons)) {
            $context->addViolation('Merci de renseigner la raison de votre signalement.');
        }
    }
}

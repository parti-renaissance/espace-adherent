<?php

namespace AppBundle\Report;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReportCommand
{
    private $subject;
    private $author;

    /**
     * @Assert\Choice(
     *     choices=Report::REASONS_LIST,
     *     strict=true,
     *     multiple=true,
     *     multipleMessage="report.invalid_reasons",
     *     min=1,
     *     minMessage="report.invalid_reasons"
     * )
     */
    private $reasons = [];

    /**
     * @Assert\Length(min=10, max=1000)
     */
    private $comment;

    public function __construct(ReportableInterface $subject, Adherent $author)
    {
        $this->subject = $subject;
        $this->author = $author;
    }

    public function getSubject(): ReportableInterface
    {
        return $this->subject;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
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

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @Assert\Callback
     */
    public function validateComment(ExecutionContextInterface $context): void
    {
        if ($this->comment && !\in_array(Report::REASON_OTHER, $this->reasons, true)) {
            $context
                ->buildViolation('Vous devez cocher la case "Autre" afin de renseigner un commentaire.')
                ->atPath('comment')
                ->addViolation()
            ;

            return;
        }

        if (!$this->comment && \in_array(Report::REASON_OTHER, $this->reasons, true)) {
            $context
                ->buildViolation('Merci de renseigner la raison de votre signalement.')
                ->atPath('comment')
                ->addViolation()
            ;
        }
    }
}

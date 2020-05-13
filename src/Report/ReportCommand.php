<?php

namespace App\Report;

use App\Entity\Adherent;
use App\Entity\Report\ReportableInterface;
use App\Entity\Report\ReportReasonEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ReportCommand
{
    private $subject;
    private $author;

    /**
     * @Assert\Choice(
     *     choices=ReportReasonEnum::REASONS_LIST,
     *     strict=true,
     *     multiple=true,
     *     multipleMessage="report.invalid_reasons",
     *     min=1,
     *     minMessage="report.invalid_reasons"
     * )
     */
    private $reasons = [];

    /**
     * @Assert\Length(max=1000)
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
        if ($this->comment && !\in_array(ReportReasonEnum::REASON_OTHER, $this->reasons, true)) {
            $context
                ->buildViolation('Vous devez cocher la case "Autre" afin de renseigner un commentaire.')
                ->atPath('comment')
                ->addViolation()
            ;

            return;
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Validator;

use App\Renaissance\Newsletter\SubscriptionRequest;
use App\Repository\Renaissance\NewsletterSubscriptionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueRenaissanceNewsletterValidator extends ConstraintValidator
{
    private NewsletterSubscriptionRepository $repository;

    public function __construct(NewsletterSubscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueRenaissanceNewsletter) {
            throw new UnexpectedTypeException($constraint, UniqueRenaissanceNewsletter::class);
        }

        if (!$value instanceof SubscriptionRequest) {
            throw new UnexpectedValueException($value, SubscriptionRequest::class);
        }

        if (!$value->email) {
            return;
        }

        if ($this->repository->findOneByEmail($value->email)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}

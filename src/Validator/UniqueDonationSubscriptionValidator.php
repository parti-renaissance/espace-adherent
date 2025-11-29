<?php

declare(strict_types=1);

namespace App\Validator;

use App\Donation\Request\DonationRequest;
use App\Repository\DonationRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueDonationSubscriptionValidator extends ConstraintValidator
{
    private $donationRepository;
    private $urlGenerator;
    private $authorizationChecker;

    public function __construct(
        DonationRepository $donationRepository,
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $this->donationRepository = $donationRepository;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param DonationRequest            $value
     * @param UniqueDonationSubscription $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueDonationSubscription) {
            throw new UnexpectedTypeException($constraint, UniqueDonationSubscription::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DonationRequest) {
            throw new UnexpectedValueException($value, DonationRequest::class);
        }

        if (!$value->isSubscription()) {
            return;
        }

        if ($this->donationRepository->findAllSubscribedDonationByEmail($value->getEmailAddress())) {
            $this->context
                ->buildViolation($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $constraint->message : $constraint->messageForAnonymous)
                ->setParameters([
                    '{{ profile_url }}' => $this->urlGenerator->generate('app_my_donations_show_list'),
                ])
                ->addViolation()
            ;
        }
    }
}

<?php

namespace App\Validator;

use App\Donation\DonationRequest;
use App\Repository\DonationRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDonationSubscriptionValidator extends ConstraintValidator
{
    private $donationRepository;
    private $urlGenerator;
    private $authorizationChecker;

    public function __construct(
        DonationRepository $donationRepository,
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->donationRepository = $donationRepository;
        $this->urlGenerator = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param DonationRequest            $value
     * @param UniqueDonationSubscription $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueDonationSubscription) {
            throw new UnexpectedTypeException($constraint, UniqueDonationSubscription::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof DonationRequest) {
            throw new UnexpectedTypeException($value, DonationRequest::class);
        }

        if (!$value->isSubscription()) {
            return;
        }

        if ($this->donationRepository->findAllSubscribedDonationByEmail($value->getEmailAddress())) {
            $this->context
                ->buildViolation($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') ? $constraint->message : $constraint->messageForAnonymous)
                ->setParameters([
                    '{{ profile_url }}' => $this->urlGenerator->generate('app_user_profile'),
                    '{{ donation_url }}' => $this->urlGenerator->generate(
                        'donation_informations',
                        ['montant' => $value->getAmount()]
                    ),
                ])
                ->addViolation()
            ;
        }
    }
}

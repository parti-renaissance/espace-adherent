<?php

namespace AppBundle\Validator;

use AppBundle\Donation\DonationRequest;
use AppBundle\Repository\DonationRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueDonationSubscriptionValidator extends ConstraintValidator
{
    private $donationRepository;
    private $urlGenerator;

    public function __construct(DonationRepository $donationRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->donationRepository = $donationRepository;
        $this->urlGenerator = $urlGenerator;
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
                ->buildViolation($constraint->message)
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

<?php

namespace AppBundle\Controller\ArgumentValueResolver;

use AppBundle\Donation\DonationRequestFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipOnBoardingInterface;
use AppBundle\Membership\OnBoarding\RegisteringAdherent;
use AppBundle\Membership\OnBoarding\RegisteringDonation;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MembershipOnBoardingResolver implements ArgumentValueResolverInterface
{
    private $adherentRepository;
    private $donationRequestFactory;

    public function __construct(AdherentRepository $adherentRepository, DonationRequestFactory $donationRequestFactory)
    {
        $this->adherentRepository = $adherentRepository;
        $this->donationRequestFactory = $donationRequestFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $request->hasSession() && is_subclass_of($argument->getType(), MembershipOnBoardingInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if (!$adherentId = $request->getSession()->get(MembershipOnBoardingInterface::NEW_ADHERENT_ID)) {
            throw new NotFoundHttpException('The adherent has not been successfully redirected from the registration page.');
        }

        $adherent = $this->adherentRepository->find($adherentId);

        if (!$adherent instanceof Adherent) {
            throw new NotFoundHttpException(sprintf('New adherent not found for id %s".', $adherentId));
        }

        if (RegisteringAdherent::class === $argument->getType()) {
            yield new RegisteringAdherent($adherent);
        }

        if (RegisteringDonation::class === $argument->getType()) {
            yield new RegisteringDonation($this->donationRequestFactory->createFromAdherent($adherent));
        }
    }
}

<?php

namespace AppBundle\Controller\ArgumentValueResolver;

use AppBundle\Donation\DonationRequestFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Membership\MembershipOnBoardingInterface;
use AppBundle\Membership\OnBoarding\OnBoardingAdherent;
use AppBundle\Membership\OnBoarding\OnBoardingDonation;
use AppBundle\Membership\OnBoarding\OnBoardingSessionHandler;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MembershipOnBoardingResolver implements ArgumentValueResolverInterface
{
    private $sessionHandler;
    private $adherentRepository;
    private $donationRequestFactory;

    public function __construct(
        OnBoardingSessionHandler $sessionHandler,
        AdherentRepository $adherentRepository,
        DonationRequestFactory $donationRequestFactory
    ) {
        $this->sessionHandler = $sessionHandler;
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
        if (!$adherentId = $this->sessionHandler->getNewAdherentId($request->getSession())) {
            throw new NotFoundHttpException('The adherent has not been successfully redirected from the registration page.');
        }

        $adherent = $this->adherentRepository->find($adherentId);

        if (!$adherent instanceof Adherent) {
            throw new NotFoundHttpException(sprintf('New adherent not found for id "%s".', $adherentId));
        }

        $type = $argument->getType();

        if (OnBoardingAdherent::class === $type) {
            yield new OnBoardingAdherent($adherent);
        }

        if (OnBoardingDonation::class === $type) {
            yield new OnBoardingDonation($this->donationRequestFactory->createFromAdherent($adherent));
        }
    }
}

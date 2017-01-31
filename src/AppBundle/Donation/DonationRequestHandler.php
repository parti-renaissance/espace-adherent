<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DonationRequestHandler
{
    private $dispatcher;
    private $manager;
    private $donationFactory;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ManagerRegistry $doctrine,
        DonationFactory $donationFactory
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $doctrine->getManagerForClass(Donation::class);
        $this->donationFactory = $donationFactory;
    }

    public function handle(DonationRequest $request, string $clientIp): Donation
    {
        $donation = $this->donationFactory->createFromDonationRequest($request);
        $donation->init($clientIp);

        $this->dispatcher->dispatch(DonationEvents::CREATED, new DonationWasCreatedEvent($donation));

        $this->manager->persist($donation);
        $this->manager->flush();

        return $donation;
    }
}

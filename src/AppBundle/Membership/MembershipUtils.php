<?php

namespace AppBundle\Membership;

use AppBundle\Donation\DonationRequest;
use AppBundle\Donation\DonationRequestFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class MembershipUtils implements EventSubscriberInterface
{
    const REGISTERING_DONATION = 'membership.registering_donation';
    const NEW_ADHERENT_ID = 'membership.new_adherent_id';

    private $factory;
    private $session;

    public function __construct(DonationRequestFactory $factory, SessionInterface $session)
    {
        $this->factory = $factory;
        $this->session = $session;
    }

    public function getRegisteringDonation(): ?DonationRequest
    {
        return $this->session->get(self::REGISTERING_DONATION);
    }

    public function clearRegisteringDonation(): void
    {
        $this->session->remove(self::REGISTERING_DONATION);
    }

    /**
     * Returns the id of the new adherent during registration's steps.
     */
    public function getNewAdherentId(): ?int
    {
        return $this->session->get(self::NEW_ADHERENT_ID);
    }

    /**
     * If true, the current user has started the subscription process but hasn't finished yet.
     */
    public function isInSubscriptionProcess(): bool
    {
        return (bool) $this->getNewAdherentId();
    }

    public function onAdherentAccountRegistrationCompleted(AdherentAccountWasCreatedEvent $event): void
    {
        $donationRequest = $this->factory->createFromAdherent($adherent = $event->getAdherent());

        $this->session->set(self::REGISTERING_DONATION, $donationRequest);
        $this->session->set(self::NEW_ADHERENT_ID, $adherent->getId());
    }

    public function clearNewAdherentId(): void
    {
        $this->session->remove(self::NEW_ADHERENT_ID);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AdherentEvents::REGISTRATION_COMPLETED => ['onAdherentAccountRegistrationCompleted', 10],
        ];
    }
}

<?php

namespace AppBundle\Membership;

use AppBundle\Donation\DonationFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class MembershipUtils
{
    const REGISTERING_DONATION = 'membership.registering_donation';
    const NEW_ADHERENT_ID = 'membership.new_adherent_id';

    private $factory;
    private $session;

    public function __construct(DonationFactory $factory, SessionInterface $session)
    {
        $this->factory = $factory;
        $this->session = $session;
    }

    public function createRegisteringDonation(Adherent $adherent)
    {
        $donation = $this->factory->createDonationFromAdherent($adherent);

        $this->session->set(self::REGISTERING_DONATION, $donation);
        $this->session->set(self::NEW_ADHERENT_ID, $adherent->getId());
    }

    /**
     * @return Donation|null
     */
    public function getRegisteringDonation()
    {
        return $this->session->get(self::REGISTERING_DONATION);
    }

    public function clearRegisteringDonation()
    {
        $this->session->remove(self::REGISTERING_DONATION);
    }

    /**
     * Returns the id of the new adherent during registration's steps.
     *
     * @return int|null
     */
    public function getNewAdherentId()
    {
        return $this->session->get(self::NEW_ADHERENT_ID);
    }

    public function clearNewAdherentId()
    {
        $this->session->remove(self::NEW_ADHERENT_ID);
    }
}

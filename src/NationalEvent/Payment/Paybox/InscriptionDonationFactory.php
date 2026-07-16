<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Paybox;

use App\Address\AddressInterface;
use App\Donation\DonationSourceEnum;
use App\Donation\DonatorManager;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\NationalEvent\Payment;
use App\Entity\PostAddress;
use App\Repository\DonatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Materialises a national event payment as a donation, so it can ride the existing Paybox rail.
 *
 * The donation is deliberately built by hand rather than through DonationRequestHandler: that one requires a
 * DonationRequest (carrying the fiscal ceiling validators) and emits DonationWasCreatedEvent, which would geocode
 * the address and attach the adherent. None of that applies to an event inscription.
 */
class InscriptionDonationFactory
{
    // Paybox needs a complete billing address for 3-D Secure, but an inscription only carries a postal code.
    // The party HQ address is used for every inscription: one address means one uniform 3-D Secure behaviour.
    private const HQ_STREET = '11 avenue Robert Schuman';
    private const HQ_CITY_CODE = '75007-75107'; // <postal>-<INSEE>, as createFrenchAddress() explodes on the dash
    private const HQ_CITY_NAME = 'Paris';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DonatorRepository $donatorRepository,
        private readonly DonatorManager $donatorManager,
        private readonly DonationRequestUtils $donationRequestUtils,
    ) {
    }

    public function createForPayment(Payment $payment): Donation
    {
        $inscription = $payment->inscription;

        $donator = $this->donatorRepository->findOneForMatching(
            $inscription->addressEmail,
            $inscription->firstName,
            $inscription->lastName
        );

        if (!$donator) {
            $donator = new Donator(
                $inscription->lastName,
                $inscription->firstName,
                self::HQ_CITY_NAME,
                AddressInterface::FRANCE,
                $inscription->addressEmail,
                $inscription->gender
            );
            $donator->setIdentifier($this->donatorManager->incrementIdentifier());
        }

        $donation = new Donation(
            $uuid = Uuid::v4(),
            Donation::TYPE_CB,
            // Both sides are already in cents: converting here would charge 100x the price.
            $payment->amount,
            new \DateTime(),
            PostAddress::createFrenchAddress(self::HQ_STREET, self::HQ_CITY_CODE, self::HQ_CITY_NAME),
            null,
            PayboxPaymentSubscription::NONE,
            $this->donationRequestUtils->buildDonationReference(
                $uuid,
                $inscription->firstName.' '.$inscription->lastName
            ),
            null,
            null,
            $donator
        );

        // Sets source only: membership stays false, which is what keeps every isMembership() branch inert.
        $donation->setSource(DonationSourceEnum::NATIONAL_EVENT);

        $donator->addDonation($donation);
        $payment->donation = $donation;

        $this->entityManager->persist($donator);
        $this->entityManager->persist($donation);
        $this->entityManager->flush();

        return $donation;
    }
}

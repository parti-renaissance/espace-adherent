<?php

namespace App\DataFixtures\ORM;

use App\Address\AddressInterface;
use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\TaxReceipt;
use App\Entity\Transaction;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class LoadDonationData extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly SlugifyInterface $slugify,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Donator[] $donators */
        $donators = [
            $donator0 = $this->createDonator('000050', $this->getReference('adherent-1', Adherent::class)),
            $donator1 = $this->createDonator('000051', $this->getReference('adherent-4', Adherent::class)),
            $donator2 = $this->createDonator('000052', $this->getReference('adherent-3', Adherent::class)),
            $donator3 = $this->createDonator('000053', $this->getReference('adherent-5', Adherent::class)),
            $this->createDonator('000054', $this->getReference('renaissance-user-2', Adherent::class)),
        ];

        $currentYear = date('Y');

        foreach ($donators as $index => $donator) {
            foreach (range($currentYear - ($index + 1), $currentYear) as $year) {
                $donation = $this->createDonation(
                    $donator,
                    30,
                    PayboxPaymentSubscription::NONE,
                    Donation::TYPE_CB,
                    $year.'/01/01 10:30:00',
                    '123456'
                );
                $this->createTransaction($donation);

                $donator->addTaxReceipt(new TaxReceipt($donator, $year.'.pdf', Uuid::uuid4().'.pdf'));
                $donation->setMembership(true);
                $donator->setMembershipDonation($donation);

                $manager->persist($donation);
            }
            $donator->computeLastSuccessfulDonation();
            $manager->persist($donator);
        }

        $donationNormal = $this->createDonation(
            $donator0,
            50.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2020/01/01 10:30:00',
            '123456'
        );
        $this->createTransaction($donationNormal);

        $donationMonthly = $this->createDonation(
            $donator0,
            42.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2019/12/01 11:00:00'
        );
        $this->createTransaction($donationMonthly, Transaction::PAYBOX_CARD_UNAUTHORIZED);
        $this->createTransaction($donationMonthly, Transaction::PAYBOX_SUCCESS, '+1 month');

        $this->createDonation(
            $donator0,
            30.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CHECK,
            '2019/01/12 12:00:00'
        );

        $donation0 = $this->createDonation(
            $donator1,
            50.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2020/01/02 13:37:00',
            '654321'
        );
        $this->createTransaction($donation0);
        $donation0->setZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_75'));

        $donation1 = $this->createDonation(
            $donator2,
            50.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2019/12/04 12:00:00'
        );
        $this->createTransaction($donation1);

        $donation2 = $this->createDonation(
            $donator2,
            40.,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2020/01/04 12:30:00',
            '654321'
        );
        $this->createTransaction($donation2);

        $donation3 = $this->createDonation(
            $donator2,
            60.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2019/12/05 15:00:00'
        );
        $this->createTransaction($donation3);
        $this->createTransaction($donation3, Transaction::PAYBOX_SUCCESS, '+1 month');
        $donation3->stopSubscription();

        $donation4 = $this->createDonation(
            $donator2,
            100.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2020/01/06 19:00:00'
        );
        $this->createTransaction($donation4);

        $donation5 = $this->createDonation(
            $donator3,
            50.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2023/01/06 19:00:00'
        );
        $this->createTransaction($donation5);

        $donator4 = $this->createDonator('000055', $this->getReference('renaissance-user-4', Adherent::class));
        $donation = $this->createDonation(
            $donator4,
            30,
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CB,
            '2021/02/02 00:00:00',
            '123456'
        );
        $this->createTransaction($donation);

        $donation->setMembership(true);
        $donator4->setMembershipDonation($donation);

        $donation = $this->createDonation(
            $donator5 = $this->createDonator('000056', $this->getReference('president-ad-1', Adherent::class)),
            100.,
            PayboxPaymentSubscription::UNLIMITED,
            Donation::TYPE_CB,
            '2020/01/06 19:00:00'
        );
        $this->createTransaction($donation);

        foreach ([
            $donator0,
            $donator1,
            $donator2,
            $donator3,
            $donator4,
            $donator5,
        ] as $donator) {
            $donator->computeLastSuccessfulDonation();
            $manager->persist($donator);
            $manager->flush();

            if ($donator->isAdherent()) {
                $this->bus->dispatch(new RefreshAdherentTagCommand($donator->getAdherent()->getUuid()));
            }
        }
    }

    public function createDonator(string $accountId, Adherent $adherent): Donator
    {
        $donator = new Donator(
            $adherent->getLastName(),
            $adherent->getFirstName(),
            $adherent->getCityName(),
            $adherent->getCountry(),
            $adherent->getEmailAddress(),
            $adherent->getGender()
        );

        $donator->setIdentifier($accountId);
        $donator->setAdherent($adherent);

        return $donator;
    }

    public function createDonation(
        Donator $donator,
        float $amount = 50.0,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB,
        ?string $donatedAt = null,
        ?string $code = null,
    ): Donation {
        $donation = new Donation(
            $uuid = Uuid::uuid4(),
            $type,
            $amount * 100,
            $donatedAt ? \DateTimeImmutable::createFromFormat('Y/m/d H:i:s', $donatedAt) : new \DateTimeImmutable(),
            $donator->getAdherent()->getPostAddress(),
            '127.0.0.1',
            $duration,
            $uuid->toString().'_'.$this->slugify->slugify($donator->getFullName()),
            AddressInterface::FRANCE,
            $code,
            $donator
        );

        $donator->addDonation($donation);

        return $donation;
    }

    public function createTransaction(
        Donation $donation,
        string $resultCode = Transaction::PAYBOX_SUCCESS,
        ?string $dateModifier = null,
    ): Transaction {
        /** @var \DateTimeImmutable $donatedAt */
        $donatedAt = $donation->getDonatedAt();

        if ($dateModifier) {
            $donatedAt = $donatedAt->modify($dateModifier);
        }

        return $donation->processPayload([
            'result' => $resultCode,
            'authorization' => 'test',
            'subscription' => $donation->getDuration() ? Uuid::uuid1()->toString() : null,
            'transaction' => Uuid::uuid4()->toString(),
            'date' => $donatedAt->format('dmY'),
            'time' => $donatedAt->format('H:i:s'),
        ]);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadDonatorIdentifierData::class,
        ];
    }
}

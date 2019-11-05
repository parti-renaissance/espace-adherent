<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Address\Address;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
use AppBundle\Entity\Transaction;
use Cake\Chronos\Chronos;
use Cocur\Slugify\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDonationData extends Fixture
{
    private $slugify;

    public function __construct()
    {
        $this->slugify = Slugify::create();
    }

    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent0 */
        $adherent0 = $this->getReference('adherent-1');
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-4');
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-3');

        $donator0 = $this->createDonator('000050', $adherent0);
        $donator1 = $this->createDonator('000051', $adherent1);
        $donator2 = $this->createDonator('000052', $adherent2);

        $donationNormal = $this->createDonation($donator0);
        $donationMonthly = $this->createDonation($donator0, 42., PayboxPaymentSubscription::UNLIMITED);
        $donation0 = $this->createDonation($donator1, 50.);
        $donation1 = $this->createDonation($donator2, 50.);
        $donation2 = $this->createDonation($donator2, 40.);
        $donation3 = $this->createDonation($donator2, 60., PayboxPaymentSubscription::UNLIMITED);
        $donation4 = $this->createDonation($donator2, 100., PayboxPaymentSubscription::UNLIMITED);

        $transactionNormal = $this->createTransaction($donationNormal);
        $transactionMonthly = $this->createTransaction($donationMonthly);
        $transaction0 = $this->createTransaction($donation0);
        $transaction1 = $this->createTransaction($donation1);
        $transaction2 = $this->createTransaction($donation2);
        $transaction3 = $this->createTransaction($donation3);
        $transaction4 = $this->createTransaction($donation4);

        $donation3->stopSubscription();

        $this->setDonateAt($transaction2, '-1 day');
        $this->setDonateAt($transaction3, '-100 day');
        $this->setDonateAt($transaction4, '-50 day');

        $manager->persist($donator0);
        $manager->persist($donator1);
        $manager->persist($donator2);

        $manager->persist($donationNormal);
        $manager->persist($donationMonthly);
        $manager->persist($donation0);
        $manager->persist($donation1);
        $manager->persist($donation2);
        $manager->persist($donation3);
        $manager->persist($donation4);

        $manager->persist($transactionNormal);
        $manager->persist($transactionMonthly);
        $manager->persist($transaction0);
        $manager->persist($transaction1);
        $manager->persist($transaction2);
        $manager->persist($transaction3);
        $manager->persist($transaction4);

        $manager->flush();
    }

    public function createDonation(
        Donator $donator,
        float $amount = 50.0,
        int $duration = PayboxPaymentSubscription::NONE,
        string $type = Donation::TYPE_CB
    ): Donation {
        $donation = new Donation(
            $uuid = Uuid::uuid4(),
            $type,
            $amount * 100,
            $donator->getAdherent()->getPostAddress(),
            '127.0.0.1',
            $duration,
            $uuid->toString().'_'.$this->slugify->slugify($donator->getFullName()),
            Address::FRANCE,
            $donator
        );

        return $donation;
    }

    public function createDonator(string $accountId, Adherent $adherent): Donator
    {
        $donator = new Donator(
            $adherent->getLastName(),
            $adherent->getFirstName(),
            $adherent->getGender(),
            $adherent->getCityName(),
            $adherent->getCountry(),
            $adherent->getEmailAddress()
        );

        $donator->setIdentifier($accountId);

        $donator->setAdherent($adherent);

        return $donator;
    }

    public function createTransaction(Donation $donation): Transaction
    {
        return $donation->processPayload([
            'result' => '00000',
            'authorization' => 'test',
            'subscription' => $donation->getDuration() ? Uuid::uuid1()->toString() : null,
            'transaction' => Uuid::uuid4()->toString(),
            'date' => '02022018',
            'time' => '15:22:33',
        ]);
    }

    public function setDonateAt(Transaction $transaction, string $modifier): void
    {
        $reflectTransaction = new \ReflectionObject($transaction);
        $reflectTransactionAt = $reflectTransaction->getProperty('payboxDateTime');
        $reflectTransactionAt->setAccessible(true);
        $reflectTransactionAt->setValue($transaction, new Chronos($modifier));
        $reflectTransactionAt->setAccessible(false);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadDonatorIdentifierData::class,
        ];
    }
}

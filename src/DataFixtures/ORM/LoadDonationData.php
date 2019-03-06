<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Address\Address;
use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
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

        $donationNormal = $this->createDonation($adherent0);
        $donationMonthly = $this->createDonation($adherent0, 42., PayboxPaymentSubscription::UNLIMITED);
        $donation0 = $this->createDonation($adherent1, 50.);
        $donation1 = $this->createDonation($adherent2, 50.);
        $donation2 = $this->createDonation($adherent2, 40.);
        $donation3 = $this->createDonation($adherent2, 60., PayboxPaymentSubscription::UNLIMITED);
        $donation4 = $this->createDonation($adherent2, 100., PayboxPaymentSubscription::UNLIMITED);

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
        Adherent $adherent,
        float $amount = 50.0,
        int $duration = PayboxPaymentSubscription::NONE
    ): Donation {
        $donation = new Donation(
            $uuid = Uuid::uuid4(),
            $uuid->toString().'_'.$this->slugify->slugify($adherent->getFullName()),
            $amount * 100,
            $adherent->getGender(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getEmailAddress(),
            $adherent->getPostAddress(),
            '127.0.0.1',
            $duration,
            Address::FRANCE
        );

        return $donation;
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
        ];
    }
}

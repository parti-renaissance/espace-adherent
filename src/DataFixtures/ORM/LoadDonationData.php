<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadDonationData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /** @var Adherent $adherent1 */
        $adherent1 = $this->getReference('adherent-1');
        /** @var Adherent $adherent2 */
        $adherent2 = $this->getReference('adherent-3');

        $donation0 = $this->create($adherent1, 50.);
        $donation1 = $this->create($adherent2, 50.);
        $donation2 = $this->create($adherent2, 40.);
        $donation3 = $this->create($adherent2, 60., PayboxPaymentSubscription::UNLIMITED);
        $donation4 = $this->create($adherent2, 100., PayboxPaymentSubscription::UNLIMITED);

        $donation3->stopSubscription();

        $this->setDonateAt($donation2, '-1 day');
        $this->setDonateAt($donation3, '-100 day');
        $this->setDonateAt($donation4, '-50 day');

        $manager->persist($donation0);
        $manager->persist($donation1);
        $manager->persist($donation2);
        $manager->persist($donation3);
        $manager->persist($donation4);
        $manager->flush();
    }

    public function create(Adherent $adherent, float $amount = 50.0, int $duration = PayboxPaymentSubscription::NONE): Donation
    {
        $donation = new Donation(
            Uuid::uuid4(),
            $amount * 100,
            $adherent->getGender(),
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getEmailAddress(),
            $adherent->getPostAddress(),
            $adherent->getPhone(),
            '127.0.0.1',
            $duration
        );

        $donation->finish([
            'result' => '00000',
            'authorization' => 'test',
        ]);

        return $donation;
    }

    public function setDonateAt(Donation $donation, string $modifier): void
    {
        $reflectDonation = new \ReflectionObject($donation);
        $reflectDonationAt = $reflectDonation->getProperty('donatedAt');
        $reflectDonationAt->setAccessible(true);
        $reflectDonationAt->setValue($donation, new \DateTime($modifier));
        $reflectDonationAt->setAccessible(false);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

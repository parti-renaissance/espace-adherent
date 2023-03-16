<?php

namespace App\DataFixtures\ORM;

use App\ElectedRepresentative\Contribution\ContributionTypeEnum;
use App\Entity\ElectedRepresentative\Contribution;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadElectedRepresentativeContributionData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var ElectedRepresentative $erDepartment92 */
        $erDepartment92 = $this->getReference('elected-representative-dpt-92');

        $contribution = new Contribution();
        $contribution->startDate = new \DateTime('2023-03-15');
        $contribution->electedRepresentative = $erDepartment92;
        $contribution->gocardlessCustomerId = 'CU_DPT92';
        $contribution->gocardlessBankAccountId = 'BA_DPT92';
        $contribution->gocardlessBankAccountEnabled = true;
        $contribution->gocardlessMandateId = 'MD_DPT92';
        $contribution->gocardlessMandateStatus = 'active';
        $contribution->gocardlessSubscriptionId = 'SB_DPT82';
        $contribution->gocardlessSubscriptionStatus = 'active';
        $contribution->type = ContributionTypeEnum::MANDATE;

        $manager->persist($contribution);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadElectedRepresentativeData::class,
        ];
    }
}

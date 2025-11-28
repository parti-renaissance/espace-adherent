<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Contribution\ContributionStatusEnum;
use App\Contribution\ContributionTypeEnum;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadContributionData extends Fixture implements DependentFixtureInterface
{
    public const CONTRIBUTION_01_UUID = '788ce91b-6bc4-424c-b6e4-acbf65d68046';

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $erDepartment92 */
        $erDepartment92 = $this->getReference('renaissance-user-2', Adherent::class);

        $contribution = new Contribution(Uuid::fromString(self::CONTRIBUTION_01_UUID));
        $contribution->startDate = $contributionDate = new \DateTime('2023-03-15');
        $contribution->adherent = $erDepartment92;
        $contribution->gocardlessCustomerId = 'CU_DPT92';
        $contribution->gocardlessBankAccountId = 'BA_DPT92';
        $contribution->gocardlessBankAccountEnabled = true;
        $contribution->gocardlessMandateId = 'MD_DPT92';
        $contribution->gocardlessMandateStatus = 'active';
        $contribution->gocardlessSubscriptionId = 'SB_DPT82';
        $contribution->gocardlessSubscriptionStatus = 'active';
        $contribution->type = ContributionTypeEnum::MANDATE;

        $erDepartment92->setLastContribution($contribution);
        $erDepartment92->setContributionStatus(ContributionStatusEnum::ELIGIBLE);
        $erDepartment92->setContributedAt($contributionDate);
        $erDepartment92->addRevenueDeclaration(10000);

        $manager->persist($contribution);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

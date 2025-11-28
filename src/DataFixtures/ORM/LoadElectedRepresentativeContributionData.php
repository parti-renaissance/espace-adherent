<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\ElectedRepresentative\Contribution\ContributionStatusEnum;
use App\ElectedRepresentative\Contribution\ContributionTypeEnum;
use App\Entity\ElectedRepresentative\Contribution;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadElectedRepresentativeContributionData extends Fixture implements DependentFixtureInterface
{
    public const CONTRIBUTION_01_UUID = '117921c2-93ce-4307-8364-709fd34de79c';

    public function load(ObjectManager $manager): void
    {
        /** @var ElectedRepresentative $erDepartment92 */
        $erDepartment92 = $this->getReference('elected-representative-dpt-92', ElectedRepresentative::class);

        $contribution = new Contribution(Uuid::fromString(self::CONTRIBUTION_01_UUID));
        $contribution->startDate = $contributionDate = new \DateTime('2023-03-15');
        $contribution->electedRepresentative = $erDepartment92;
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

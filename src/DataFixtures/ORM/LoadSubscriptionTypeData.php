<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\SubscriptionType;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSubscriptionTypeData extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getSubscriptionTypes() as $row) {
            $type = new SubscriptionType($row['label'], $row['code'], $row['externalId'] ?? null);
            $type->setPosition($row['position'] ?? 100);
            $this->setReference('st-'.$row['code'], $type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    private function getSubscriptionTypes(): array
    {
        return [
            [
                'label' => 'Recevoir les informations sur les actions militantes du mouvement par téléphone',
                'code' => SubscriptionTypeEnum::MILITANT_ACTION_SMS,
            ],
            [
                'label' => 'National',
                'code' => SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                'externalId' => '123abc',
                'position' => 1,
            ],
            [
                'label' => 'Mon assemblée départementale',
                'code' => SubscriptionTypeEnum::REFERENT_EMAIL,
                'position' => 3,
            ],
            [
                'label' => 'Ma circonscription',
                'code' => SubscriptionTypeEnum::DEPUTY_EMAIL,
                'position' => 4,
            ],
            [
                'label' => 'Mon comité local',
                'code' => SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                'position' => 5,
            ],
            [
                'label' => 'Mon sénateur/trice',
                'code' => SubscriptionTypeEnum::SENATOR_EMAIL,
                'position' => 6,
            ],
            [
                'label' => 'Les candidats du Parti',
                'code' => SubscriptionTypeEnum::CANDIDATE_EMAIL,
                'position' => 7,
            ],
            [
                'label' => 'Recevoir les emails des Jeunes avec Macron',
                'code' => SubscriptionTypeEnum::JAM_EMAIL,
                'position' => 8,
            ],
            [
                'label' => 'Notification nouvel événement proche de chez moi',
                'code' => SubscriptionTypeEnum::EVENT_EMAIL,
                'position' => 9,
            ],
        ];
    }
}

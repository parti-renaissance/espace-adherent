<?php

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
                'label' => 'Recevoir les emails du national',
                'code' => SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                'externalId' => '123abc',
                'position' => 1,
            ],
            [
                'label' => 'Recevoir la newsletter hebdomadaire nationale',
                'code' => SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                'externalId' => '456def',
                'position' => 2,
            ],
            [
                'label' => 'Recevoir les emails de mon Assemblée départementale',
                'code' => SubscriptionTypeEnum::REFERENT_EMAIL,
                'position' => 3,
            ],
            [
                'label' => 'Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription',
                'code' => SubscriptionTypeEnum::DEPUTY_EMAIL,
                'position' => 4,
            ],
            [
                'label' => 'Recevoir les emails de mon Comité local',
                'code' => SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                'position' => 5,
            ],
            [
                'label' => 'Recevoir les emails de ma/mon sénateur/trice',
                'code' => SubscriptionTypeEnum::SENATOR_EMAIL,
                'position' => 6,
            ],
            [
                'label' => 'Recevoir les emails des candidats du parti',
                'code' => SubscriptionTypeEnum::CANDIDATE_EMAIL,
                'position' => 7,
            ],
            [
                'label' => 'Recevoir les emails des Jeunes avec Macron',
                'code' => SubscriptionTypeEnum::JAM_EMAIL,
                'position' => 8,
            ],
            [
                'label' => 'Recevoir les emails d\'événements',
                'code' => SubscriptionTypeEnum::EVENT_EMAIL,
                'position' => 9,
            ],
        ];
    }
}

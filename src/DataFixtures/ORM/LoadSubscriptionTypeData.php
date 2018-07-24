<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\SubscriptionType;
use AppBundle\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSubscriptionTypeData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getSubscriptionTypes() as ['label' => $label, 'code' => $code]) {
            $type = new SubscriptionType($label, $code);
            $this->setReference('st-'.$code, $type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    private function getSubscriptionTypes(): array
    {
        return [
            [
                'label' => 'Recevoir les actions militantes du mouvement par SMS ou MMS',
                'code' => SubscriptionTypeEnum::MILITANT_ACTION_SMS,
            ],
            [
                'label' => 'Recevoir les informations sur le mouvement',
                'code' => SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
            ],
            [
                'label' => 'Recevoir la newsletter hebdomadaire LaREM',
                'code' => SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
            ],
            [
                'label' => 'Recevoir les e-mails de votre animateur local',
                'code' => SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
            ],
            [
                'label' => 'Recevoir les e-mails de votre référent départemental',
                'code' => SubscriptionTypeEnum::REFERENT_EMAIL,
            ],
            [
                'label' => 'Recevoir les e-mails de votre porteur de projet',
                'code' => SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL,
            ],
            [
                'label' => 'Être notifié(e) de la création de nouveaux projets citoyens',
                'code' => SubscriptionTypeEnum::CITIZEN_PROJECT_CREATION_EMAIL,
            ],
        ];
    }
}

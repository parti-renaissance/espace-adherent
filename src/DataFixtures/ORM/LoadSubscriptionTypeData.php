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
        foreach ($this->getSubscriptionTypes() as ['label' => $label, 'code' => $code, 'externalId' => $externalId]) {
            $type = new SubscriptionType($label, $code, $externalId);
            $this->setReference('st-'.$code, $type);
            $manager->persist($type);
        }

        $manager->flush();
    }

    private function getSubscriptionTypes(): array
    {
        return [
            [
                'label' => 'Recevoir les informations sur les actions militantes du mouvement par SMS ou MMS',
                'code' => SubscriptionTypeEnum::MILITANT_ACTION_SMS,
                'externalId' => null,
            ],
            [
                'label' => 'Recevoir les e-mails nationaux',
                'code' => SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL,
                'externalId' => '123abc',
            ],
            [
                'label' => 'Recevoir la newsletter hebdomadaire nationale',
                'code' => SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL,
                'externalId' => '456def',
            ],
            [
                'label' => 'Recevoir les e-mails de mon/ma candidat(e) aux municipales 2020',
                'code' => SubscriptionTypeEnum::MUNICIPAL_EMAIL,
                'externalId' => null,
            ],
            [
                'label' => 'Recevoir les e-mails de mon/ma député(e)',
                'code' => SubscriptionTypeEnum::DEPUTY_EMAIL,
                'externalId' => null,
            ],
            [
                'label' => 'Recevoir les e-mails de mon animateur(trice) local(e) de comité',
                'code' => SubscriptionTypeEnum::LOCAL_HOST_EMAIL,
                'externalId' => null,
            ],
            [
                'label' => 'Recevoir les e-mails de mon/ma référent(e) territorial(e)',
                'code' => SubscriptionTypeEnum::REFERENT_EMAIL,
                'externalId' => null,
            ],
            [
                'label' => 'Recevoir les e-mails de mon porteur de projet',
                'code' => SubscriptionTypeEnum::CITIZEN_PROJECT_HOST_EMAIL,
                'externalId' => null,
            ],
            [
                'label' => 'Être notifié(e) de la création de nouveaux projets citoyens dans un rayon de autour de chez moi',
                'code' => SubscriptionTypeEnum::CITIZEN_PROJECT_CREATION_EMAIL,
                'externalId' => null,
            ],
        ];
    }
}

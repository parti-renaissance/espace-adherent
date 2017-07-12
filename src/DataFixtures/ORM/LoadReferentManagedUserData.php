<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\Projection\ReferentManagedUserFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadReferentManagedUserData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $referentManagedUserFactory = $this->getReferentManagedUserFactory();

        $managedUser1 = $referentManagedUserFactory->createFromArray([
            'status' => ReferentManagedUser::STATUS_READY,
            'type' => ReferentManagedUser::TYPE_ADHERENT,
            'original_id' => $this->getReference('adherent-1')->getId(),
            'email' => $this->getReference('adherent-1')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-1')->getPostalCode(),
            'city' => $this->getReference('adherent-1')->getCity(),
            'country' => $this->getReference('adherent-1')->getCountry(),
            'first_name' => $this->getReference('adherent-1')->getFirstName(),
            'last_name' => $this->getReference('adherent-1')->getLastName(),
            'birthday' => $this->getReference('adherent-1')->getBirthdate(),
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_mail_subscriber' => 1,
            'created_at' => '2017-06-01 09:22:45',
        ]);

        $managedUser2 = $referentManagedUserFactory->createFromArray([
            'status' => ReferentManagedUser::STATUS_READY,
            'type' => ReferentManagedUser::TYPE_ADHERENT,
            'original_id' => $this->getReference('adherent-13')->getId(),
            'email' => $this->getReference('adherent-13')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-13')->getPostalCode(),
            'city' => $this->getReference('adherent-13')->getCity(),
            'country' => $this->getReference('adherent-13')->getCountry(),
            'first_name' => $this->getReference('adherent-13')->getFirstName(),
            'last_name' => $this->getReference('adherent-13')->getLastName(),
            'birthday' => $this->getReference('adherent-13')->getBirthdate(),
            'committees' => 'En Marche - Suisse',
            'is_committee_member' => 1,
            'is_committee_host' => 0,
            'is_mail_subscriber' => 1,
            'created_at' => '2017-06-02 15:34:12',
        ]);

        $managedUser3 = $referentManagedUserFactory->createFromArray([
            'status' => ReferentManagedUser::STATUS_READY,
            'type' => ReferentManagedUser::TYPE_NEWSLETTER,
            'original_id' => $this->getReference('news-sub-77')->getId(),
            'email' => $this->getReference('news-sub-77')->getEmail(),
            'postal_code' => $this->getReference('news-sub-77')->getPostalCode(),
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_mail_subscriber' => 1,
            'created_at' => '2017-06-03 07:58:02',
        ]);

        $managedUser4 = $referentManagedUserFactory->createFromArray([
            'status' => ReferentManagedUser::STATUS_READY,
            'type' => ReferentManagedUser::TYPE_NEWSLETTER,
            'original_id' => $this->getReference('news-sub-92')->getId(),
            'email' => $this->getReference('news-sub-92')->getEmail(),
            'postal_code' => $this->getReference('news-sub-92')->getPostalCode(),
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_mail_subscriber' => 1,
            'created_at' => '2017-06-03 11:01:56',
        ]);

        $manager->persist($managedUser1);
        $manager->persist($managedUser2);
        $manager->persist($managedUser3);
        $manager->persist($managedUser4);

        $manager->flush();
    }

    private function getReferentManagedUserFactory(): ReferentManagedUserFactory
    {
        return new ReferentManagedUserFactory();
    }

    public function getOrder()
    {
        return 5;
    }
}

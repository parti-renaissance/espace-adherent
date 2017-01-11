<?php

namespace Tests\AppBundle;

use AppBundle\Entity\ActivationKey;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Invite;
use AppBundle\Entity\MailjetEmail;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Repository\ActivationKeyRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\InvitationRepository;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait TestHelperTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function getManagerRegistry(): ManagerRegistry
    {
        if ($this->container->has('doctrine')) {
            return $this->container->get('doctrine');
        }
    }

    public function getEntityManager($class): ObjectManager
    {
        return $this->getManagerRegistry()->getManagerForClass($class);
    }

    public function getRepository($class): ObjectRepository
    {
        return $this->getManagerRegistry()->getRepository($class);
    }

    public function getActivationKeyRepository(): ActivationKeyRepository
    {
        return $this->getRepository(ActivationKey::class);
    }

    public function getAdherentRepository(): AdherentRepository
    {
        return $this->getRepository(Adherent::class);
    }

    public function getDonationRepository(): DonationRepository
    {
        return $this->getRepository(Donation::class);
    }

    public function getInvitationRepository(): InvitationRepository
    {
        return $this->getRepository(Invite::class);
    }

    public function getNewsletterSubscriptionRepository(): NewsletterSubscriptionRepository
    {
        return $this->getRepository(NewsletterSubscription::class);
    }

    public function getMailjetEmailRepository(): MailjetEmailRepository
    {
        return $this->getRepository(MailjetEmail::class);
    }
}

<?php

namespace Tests\AppBundle;

use AppBundle\Committee\CommitteeFeedHandler;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Invite;
use AppBundle\Entity\MailjetEmail;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\AdherentResetPasswordTokenRepository;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\CommitteeEventRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\InvitationRepository;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use libphonenumber\PhoneNumber;
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
        return $this->container->get('doctrine');
    }

    public function getEntityManager($class): ObjectManager
    {
        return $this->getManagerRegistry()->getManagerForClass($class);
    }

    public function getRepository($class): ObjectRepository
    {
        return $this->getManagerRegistry()->getRepository($class);
    }

    public function getActivationTokenRepository(): AdherentActivationTokenRepository
    {
        return $this->getRepository(AdherentActivationToken::class);
    }

    public function getResetPasswordTokenRepository(): AdherentResetPasswordTokenRepository
    {
        return $this->getRepository(AdherentResetPasswordToken::class);
    }

    public function getAdherentRepository(): AdherentRepository
    {
        return $this->getRepository(Adherent::class);
    }

    public function getCommitteeRepository(): CommitteeRepository
    {
        return $this->getRepository(Committee::class);
    }

    public function getCommitteeEventRepository(): CommitteeEventRepository
    {
        return $this->getRepository(CommitteeEvent::class);
    }

    public function getCommitteeFeedItemRepository(): CommitteeFeedItemRepository
    {
        return $this->getRepository(CommitteeFeedItem::class);
    }

    public function getCommitteeMembershipRepository(): CommitteeMembershipRepository
    {
        return $this->getRepository(CommitteeMembership::class);
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

    public function getCommitteeFeedHandler(): CommitteeFeedHandler
    {
        return $this->container->get('app.committee.committee_feed_handler');
    }

    /**
     * @return Adherent|null
     */
    protected function getAdherent(string $uuid)
    {
        return $this->getAdherentRepository()->findByUuid($uuid);
    }

    /**
     * @param string|null $email E-mail used to generate a unique UUID
     *
     * @return Adherent
     */
    protected function createAdherent(string $email = null)
    {
        $email = $email ?: 'john.smith@example.org';
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return new Adherent(
            Adherent::createUuid($email),
            $email,
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositions::STUDENT,
            PostAddress::createFrenchAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );
    }
}

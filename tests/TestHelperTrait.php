<?php

namespace Tests\AppBundle;

use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\ActivitySubscription;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Invite;
use AppBundle\Entity\JeMarcheReport;
use AppBundle\Entity\MailjetEmail;
use AppBundle\Entity\NewsletterInvite;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Entity\PostAddress;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\Summary;
use AppBundle\Entity\TonMacronChoice;
use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Repository\ActivitySubscriptionRepository;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\AdherentResetPasswordTokenRepository;
use AppBundle\Repository\CitizenInitiativeRepository;
use AppBundle\Repository\EventRegistrationRepository;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\InvitationRepository;
use AppBundle\Repository\JeMarcheReportRepository;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\NewsletterInviteRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use AppBundle\Repository\ProcurationProxyRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use AppBundle\Repository\Projection\ReferentManagedUserRepository;
use AppBundle\Repository\SummaryRepository;
use AppBundle\Repository\TonMacronChoiceRepository;
use AppBundle\Repository\TonMacronFriendInvitationRepository;
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

    /** @var Adherent[] */
    protected $adherents;

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function assertMailCountRecipients(int $count, ?MailjetEmail $mail): void
    {
        $this->assertNotNull($mail);
        $this->assertCount($count, $mail->getRecipients());
    }

    public function assertCountMails(int $count, string $type, ?string $recipient = null): void
    {
        $repository = $this->getMailjetEmailRepository();

        if ($recipient) {
            $this->assertCount($count, $repository->findRecipientMessages($type, $recipient));

            return;
        }

        $this->assertCount($count, $repository->findMessages($type));
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

    public function getEventRepository(): EventRepository
    {
        return $this->getRepository(Event::class);
    }

    public function getCitizenInitiativeRepository(): CitizenInitiativeRepository
    {
        return $this->getRepository(CitizenInitiative::class);
    }

    public function getActivitySubscriptionRepository(): ActivitySubscriptionRepository
    {
        return $this->getRepository(ActivitySubscription::class);
    }

    public function getCommitteeFeedItemRepository(): CommitteeFeedItemRepository
    {
        return $this->getRepository(CommitteeFeedItem::class);
    }

    public function getCommitteeMembershipRepository(): CommitteeMembershipRepository
    {
        return $this->getRepository(CommitteeMembership::class);
    }

    public function getEventRegistrationRepository(): EventRegistrationRepository
    {
        return $this->getRepository(EventRegistration::class);
    }

    public function getDonationRepository(): DonationRepository
    {
        return $this->getRepository(Donation::class);
    }

    public function getInvitationRepository(): InvitationRepository
    {
        return $this->getRepository(Invite::class);
    }

    public function getNewsletterInvitationRepository(): NewsletterInviteRepository
    {
        return $this->getRepository(NewsletterInvite::class);
    }

    public function getNewsletterSubscriptionRepository(): NewsletterSubscriptionRepository
    {
        return $this->getRepository(NewsletterSubscription::class);
    }

    public function getJeMarcheReportRepository(): JeMarcheReportRepository
    {
        return $this->getRepository(JeMarcheReport::class);
    }

    public function getProcurationRequestRepository(): ProcurationRequestRepository
    {
        return $this->getRepository(ProcurationRequest::class);
    }

    public function getProcurationProxyRepository(): ProcurationProxyRepository
    {
        return $this->getRepository(ProcurationProxy::class);
    }

    public function getMailjetEmailRepository(): MailjetEmailRepository
    {
        return $this->getRepository(MailjetEmail::class);
    }

    public function getSummaryRepository(): SummaryRepository
    {
        return $this->getRepository(Summary::class);
    }

    public function getReferentManagedUserRepository(): ReferentManagedUserRepository
    {
        return $this->getRepository(ReferentManagedUser::class);
    }

    public function getTonMacronChoiceRepository(): TonMacronChoiceRepository
    {
        return $this->getRepository(TonMacronChoice::class);
    }

    public function getTonMacronInvitationRepository(): TonMacronFriendInvitationRepository
    {
        return $this->getRepository(TonMacronFriendInvitation::class);
    }

    public function getCommitteeFeedManager(): CommitteeFeedManager
    {
        return $this->container->get('app.committee.feed_manager');
    }

    protected function getAdherent(string $uuid): ?Adherent
    {
        return $this->getAdherentRepository()->findByUuid($uuid);
    }

    /**
     * @param bool $refresh Leave to false to avoid reloading from database
     *
     * @return Adherent[]
     */
    protected function getAdherents($refresh = false): array
    {
        if (null === $this->adherents || $refresh) {
            $this->adherents = [
                $this->getAdherent(LoadAdherentData::ADHERENT_1_UUID),
                $this->getAdherent(LoadAdherentData::ADHERENT_2_UUID),
            ];
        }

        return $this->adherents;
    }

    /**
     * @param string|null $email E-mail used to generate a unique UUID
     *
     * @return Adherent
     */
    protected function createAdherent(string $email = null): Adherent
    {
        $email = $email ?: 'john.smith@example.org';
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return new Adherent(
            Adherent::createUuid($email),
            $email,
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

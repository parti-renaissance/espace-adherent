<?php

namespace Tests\AppBundle;

use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\Administrator;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Donation;
use AppBundle\Entity\Invite;
use AppBundle\Entity\JeMarcheReport;
use AppBundle\Entity\Email;
use AppBundle\Entity\NewsletterInvite;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Entity\PostAddress;
use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Entity\PurchasingPowerChoice;
use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Entity\Reporting\CommitteeMembershipHistory;
use AppBundle\Entity\RepublicanSilence;
use AppBundle\Entity\SubscriptionType;
use AppBundle\Entity\Summary;
use AppBundle\Entity\TonMacronChoice;
use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\TurnkeyProject;
use AppBundle\Membership\ActivityPositions;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\AdherentResetPasswordTokenRepository;
use AppBundle\Repository\AdministratorRepository;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\CitizenProjectCommentRepository;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\DonationRepository;
use AppBundle\Repository\EmailRepository;
use AppBundle\Repository\EmailSubscriptionHistoryRepository;
use AppBundle\Repository\EventRegistrationRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\IdeasWorkshop\IdeaRepository;
use AppBundle\Repository\InvitationRepository;
use AppBundle\Repository\JeMarcheReportRepository;
use AppBundle\Repository\NewsletterInviteRepository;
use AppBundle\Repository\NewsletterSubscriptionRepository;
use AppBundle\Repository\ProcurationProxyRepository;
use AppBundle\Repository\ProcurationRequestRepository;
use AppBundle\Repository\PurchasingPowerChoiceRepository;
use AppBundle\Repository\PurchasingPowerInvitationRepository;
use AppBundle\Repository\SubscriptionTypeRepository;
use AppBundle\Repository\SummaryRepository;
use AppBundle\Repository\ThreadCommentRepository;
use AppBundle\Repository\ThreadRepository;
use AppBundle\Repository\TonMacronChoiceRepository;
use AppBundle\Repository\TonMacronFriendInvitationRepository;
use AppBundle\Repository\TransactionRepository;
use AppBundle\Repository\TurnkeyProjectRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use League\Flysystem\Filesystem;
use League\Glide\Server;
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

    public function assertMailCountRecipients(int $count, ?Email $mail): void
    {
        $this->assertNotNull($mail);
        $this->assertCount($count, $mail->getRecipients());
    }

    public function assertCountMails(int $count, string $type, ?string $recipient = null): void
    {
        $repository = $this->getEmailRepository();

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

    public function getStorage(): Filesystem
    {
        return $this->container->get('app.storage');
    }

    public function getGlide(): Server
    {
        return $this->container->get('app.glide');
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

    protected function getAdministratorRepository(): AdministratorRepository
    {
        return $this->getRepository(Administrator::class);
    }

    public function getCommitteeRepository(): CommitteeRepository
    {
        return $this->getRepository(Committee::class);
    }

    public function getCitizenProjectRepository(): CitizenProjectRepository
    {
        return $this->getRepository(CitizenProject::class);
    }

    public function getTurnkeyProjectRepository(): TurnkeyProjectRepository
    {
        return $this->getRepository(TurnkeyProject::class);
    }

    public function getCitizenProjectCommentRepository(): CitizenProjectCommentRepository
    {
        return $this->getRepository(CitizenProjectComment::class);
    }

    public function getCitizenActionRepository(): CitizenActionRepository
    {
        return $this->getRepository(CitizenAction::class);
    }

    public function getEventRepository(): EventRepository
    {
        return $this->getRepository(Event::class);
    }

    public function getCommitteeFeedItemRepository(): CommitteeFeedItemRepository
    {
        return $this->getRepository(CommitteeFeedItem::class);
    }

    public function getCommitteeMembershipRepository(): CommitteeMembershipRepository
    {
        return $this->getRepository(CommitteeMembership::class);
    }

    public function getCommitteeMembershipHistoryRepository(): EntityRepository
    {
        return $this->getRepository(CommitteeMembershipHistory::class);
    }

    public function getEventRegistrationRepository(): EventRegistrationRepository
    {
        return $this->getRepository(EventRegistration::class);
    }

    public function getDonationRepository(): DonationRepository
    {
        return $this->getRepository(Donation::class);
    }

    public function getTransactionRepository(): TransactionRepository
    {
        return $this->getRepository(Transaction::class);
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

    public function getEmailRepository(): EmailRepository
    {
        return $this->getRepository(Email::class);
    }

    public function getSummaryRepository(): SummaryRepository
    {
        return $this->getRepository(Summary::class);
    }

    public function getTonMacronChoiceRepository(): TonMacronChoiceRepository
    {
        return $this->getRepository(TonMacronChoice::class);
    }

    public function getTonMacronInvitationRepository(): TonMacronFriendInvitationRepository
    {
        return $this->getRepository(TonMacronFriendInvitation::class);
    }

    public function getPurchasingPowerChoiceRepository(): PurchasingPowerChoiceRepository
    {
        return $this->getRepository(PurchasingPowerChoice::class);
    }

    public function getPurchasingPowerInvitationRepository(): PurchasingPowerInvitationRepository
    {
        return $this->getRepository(PurchasingPowerInvitation::class);
    }

    public function getEmailSubscriptionHistoryRepository(): EmailSubscriptionHistoryRepository
    {
        return $this->getRepository(EmailSubscriptionHistory::class);
    }

    public function getSubscriptionTypeRepository(): SubscriptionTypeRepository
    {
        return $this->getRepository(SubscriptionType::class);
    }

    public function getIdeaRepository(): IdeaRepository
    {
        return $this->getRepository(Idea::class);
    }

    public function getThreadRepository(): ThreadRepository
    {
        return $this->getRepository(Thread::class);
    }

    public function getThreadCommentRepository(): ThreadCommentRepository
    {
        return $this->getRepository(ThreadComment::class);
    }

    public function getCommitteeFeedManager(): CommitteeFeedManager
    {
        return $this->container->get('app.committee.feed_manager');
    }

    protected function getAdherent(string $uuid): ?Adherent
    {
        return $this->getAdherentRepository()->findByUuid($uuid);
    }

    protected function getCommittee(string $uuid): ?Committee
    {
        return $this->getCommitteeRepository()->findOneByUuid($uuid);
    }

    protected function getCitizenProject(string $uuid): ?CitizenProject
    {
        return $this->getCitizenProjectRepository()->findOneByUuid($uuid);
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
     */
    protected function createAdherent(string $email = null): Adherent
    {
        $email = $email ?: 'john.smith@example.org';
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return Adherent::create(
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

    /**
     * Remove all container references from all loaded services
     */
    protected function cleanupContainer($container, $exclude = ['kernel', 'libphonenumber.phone_number_util'])
    {
        if (!$container) {
            return;
        }

        $object = new \ReflectionObject($container);
        $property = $object->getProperty('services');
        $property->setAccessible(true);

        $services = $property->getValue($container) ?: [];
        foreach ($services as $id => $service) {
            if (\in_array($id, $exclude, true)) {
                continue;
            }

            $serviceObject = new \ReflectionObject($service);
            foreach ($serviceObject->getProperties() as $prop) {
                $prop->setAccessible(true);

                if ($prop->isStatic()) {
                    continue;
                }

                $prop->setValue($service, null);
            }
        }

        $property->setValue($container, null);
    }

    protected function disableRepublicanSilence(): void
    {
        $this
            ->getRepository(RepublicanSilence::class)
            ->createQueryBuilder('r')
            ->delete()
            ->getQuery()
            ->execute()
        ;
    }
}

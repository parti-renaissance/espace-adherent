<?php

namespace Tests\App;

use App\Committee\Feed\CommitteeFeedManager;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\Administrator;
use App\Entity\CitizenAction;
use App\Entity\CitizenProject;
use App\Entity\Committee;
use App\Entity\CommitteeFeedItem;
use App\Entity\CommitteeMembership;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\DonatorIdentifier;
use App\Entity\Email;
use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Entity\IdeasWorkshop\Idea;
use App\Entity\IdeasWorkshop\Thread;
use App\Entity\IdeasWorkshop\ThreadComment;
use App\Entity\InstitutionalEvent;
use App\Entity\Invite;
use App\Entity\JeMarcheReport;
use App\Entity\MyEuropeChoice;
use App\Entity\MyEuropeInvitation;
use App\Entity\NewsletterInvite;
use App\Entity\NewsletterSubscription;
use App\Entity\PostAddress;
use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Entity\ReferentSpaceAccessInformation;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\RepublicanSilence;
use App\Entity\SubscriptionType;
use App\Entity\Summary;
use App\Entity\TonMacronChoice;
use App\Entity\TonMacronFriendInvitation;
use App\Entity\Transaction;
use App\Entity\TurnkeyProject;
use App\Membership\ActivityPositions;
use App\Repository\AdherentActivationTokenRepository;
use App\Repository\AdherentRepository;
use App\Repository\AdherentResetPasswordTokenRepository;
use App\Repository\AdministratorRepository;
use App\Repository\CitizenActionRepository;
use App\Repository\CitizenProjectRepository;
use App\Repository\CommitteeFeedItemRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\Repository\DonationRepository;
use App\Repository\DonatorIdentifierRepository;
use App\Repository\DonatorRepository;
use App\Repository\EmailRepository;
use App\Repository\EmailSubscriptionHistoryRepository;
use App\Repository\EventRegistrationRepository;
use App\Repository\EventRepository;
use App\Repository\IdeasWorkshop\IdeaRepository;
use App\Repository\InstitutionalEventRepository;
use App\Repository\InvitationRepository;
use App\Repository\JeMarcheReportRepository;
use App\Repository\MyEuropeChoiceRepository;
use App\Repository\MyEuropeInvitationRepository;
use App\Repository\NewsletterInviteRepository;
use App\Repository\NewsletterSubscriptionRepository;
use App\Repository\ProcurationProxyRepository;
use App\Repository\ProcurationRequestRepository;
use App\Repository\ReferentSpaceAccessInformationRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Repository\SummaryRepository;
use App\Repository\ThreadCommentRepository;
use App\Repository\ThreadRepository;
use App\Repository\TonMacronChoiceRepository;
use App\Repository\TonMacronFriendInvitationRepository;
use App\Repository\TransactionRepository;
use App\Repository\TurnkeyProjectRepository;
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

    public function getCitizenActionRepository(): CitizenActionRepository
    {
        return $this->getRepository(CitizenAction::class);
    }

    public function getEventRepository(): EventRepository
    {
        return $this->getRepository(Event::class);
    }

    public function getInstitutionalEventRepository(): InstitutionalEventRepository
    {
        return $this->getRepository(InstitutionalEvent::class);
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

    public function getDonatorRepository(): DonatorRepository
    {
        return $this->getRepository(Donator::class);
    }

    public function getDonatorIdentifierRepository(): DonatorIdentifierRepository
    {
        return $this->getRepository(DonatorIdentifier::class);
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

    public function getMyEuropeChoiceRepository(): MyEuropeChoiceRepository
    {
        return $this->getRepository(MyEuropeChoice::class);
    }

    public function getMyEuropeInvitationRepository(): MyEuropeInvitationRepository
    {
        return $this->getRepository(MyEuropeInvitation::class);
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

    public function getReferentSpaceAccessInformationRepository(): ReferentSpaceAccessInformationRepository
    {
        return $this->getRepository(ReferentSpaceAccessInformation::class);
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

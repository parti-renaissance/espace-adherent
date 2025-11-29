<?php

declare(strict_types=1);

namespace Tests\App;

use App\Address\AddressInterface;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Entity\DonatorIdentifier;
use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\Email\EmailLog;
use App\Entity\Event\Event;
use App\Entity\Event\EventCategory;
use App\Entity\Event\EventRegistration;
use App\Entity\Filesystem\File;
use App\Entity\Invite;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\Member;
use App\Entity\NewsletterSubscription;
use App\Entity\NullablePostAddress;
use App\Entity\Pap\Building;
use App\Entity\Pap\BuildingEvent;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Pap\CampaignHistory;
use App\Entity\PostAddress;
use App\Entity\Reporting\CommitteeMembershipHistory;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\RepublicanSilence;
use App\Entity\SubscriptionType;
use App\Entity\Transaction;
use App\FranceCities\FranceCities;
use App\Membership\ActivityPositionsEnum;
use App\Repository\AdherentActivationTokenRepository;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\AdherentRepository;
use App\Repository\AdherentResetPasswordTokenRepository;
use App\Repository\AdministratorRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\CommitteeRepository;
use App\Repository\DonationRepository;
use App\Repository\DonatorIdentifierRepository;
use App\Repository\DonatorRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\Repository\Email\EmailLogRepository;
use App\Repository\EmailSubscriptionHistoryRepository;
use App\Repository\Event\EventRepository;
use App\Repository\EventRegistrationRepository;
use App\Repository\Filesystem\FileRepository;
use App\Repository\InviteRepository;
use App\Repository\MyTeam\DelegatedAccessRepository;
use App\Repository\MyTeam\MemberRepository;
use App\Repository\NewsletterSubscriptionRepository;
use App\Repository\Pap\BuildingEventRepository;
use App\Repository\Pap\BuildingRepository;
use App\Repository\Pap\CampaignHistoryRepository;
use App\Repository\Pap\CampaignRepository as PapCampaignRepository;
use App\Repository\SubscriptionTypeRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use League\Flysystem\FilesystemOperator;
use League\Glide\Server;
use libphonenumber\PhoneNumber;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait TestHelperTrait
{
    /** @var Adherent[] */
    protected $adherents;

    public function get(string $id): ?object
    {
        return static::getContainer()->get($id);
    }

    public function getParameter(string $name)
    {
        return static::getContainer()->getParameter($name);
    }

    public function assertMailCountRecipients(int $count, ?EmailLog $mail): void
    {
        $this->assertNotNull($mail);
        $this->assertCount($count, $mail->getRecipients());
    }

    /**
     * @return EmailLog[]
     */
    public function getMailMessages(string $type): array
    {
        return $this->getEmailRepository()->findMessages($type);
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

    public function assertMail(string $emailType, string $emailRecipient, array $emailContent)
    {
        $emails = $this->getEmailRepository()->findRecipientMessages($emailType, $emailRecipient);

        if (1 !== \count($emails)) {
            throw new \RuntimeException(\sprintf('I found %s email(s) instead of 1', \count($emails)));
        }

        $emailPayloadJson = $emails[0]->getRequestPayloadJson();
        $emailPayload = json_decode($emailPayloadJson, true);

        foreach ($emailContent as $key => $value) {
            self::assertArrayHasKey($key, $emailPayload);
            self::assertSame($emailPayload[$key], $value);
        }
    }

    public function getManagerRegistry(): ManagerRegistry
    {
        return $this->get('doctrine');
    }

    public function getStorage(): FilesystemOperator
    {
        return $this->get(FilesystemOperator::class);
    }

    public function getGlide(): Server
    {
        return $this->get(Server::class);
    }

    public function getEntityManager(?string $class = null): ObjectManager
    {
        if (null === $class) {
            return $this->getManagerRegistry()->getManager();
        }

        return $this->getManagerRegistry()->getManagerForClass($class);
    }

    public function getRepository(string $class): ObjectRepository
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

    public function getEventRepository(): EventRepository
    {
        return $this->getRepository(Event::class);
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

    public function getInvitationRepository(): InviteRepository
    {
        return $this->getRepository(Invite::class);
    }

    public function getNewsletterSubscriptionRepository(): NewsletterSubscriptionRepository
    {
        return $this->getRepository(NewsletterSubscription::class);
    }

    public function getEmailRepository(): EmailLogRepository
    {
        return $this->getRepository(EmailLog::class);
    }

    public function getEmailSubscriptionHistoryRepository(): EmailSubscriptionHistoryRepository
    {
        return $this->getRepository(EmailSubscriptionHistory::class);
    }

    public function getSubscriptionTypeRepository(): SubscriptionTypeRepository
    {
        return $this->getRepository(SubscriptionType::class);
    }

    public function getElectedRepresentativeRepository(): ElectedRepresentativeRepository
    {
        return $this->getRepository(ElectedRepresentative::class);
    }

    public function getFileRepository(): FileRepository
    {
        return $this->getRepository(File::class);
    }

    protected function getAdherent(string $uuid): ?Adherent
    {
        return $this->getAdherentRepository()->findOneByUuid($uuid);
    }

    protected function getCommittee(string $uuid): ?Committee
    {
        return $this->getCommitteeRepository()->findOneByUuid($uuid);
    }

    public function getCommitteeMandateRepository(): CommitteeAdherentMandateRepository
    {
        return $this->getRepository(CommitteeAdherentMandate::class);
    }

    public function getPapCampaignHistoryRepository(): CampaignHistoryRepository
    {
        return $this->getRepository(CampaignHistory::class);
    }

    public function getPapCampaignRepository(): PapCampaignRepository
    {
        return $this->getRepository(PapCampaign::class);
    }

    public function getBuildingEventRepository(): BuildingEventRepository
    {
        return $this->getRepository(BuildingEvent::class);
    }

    public function getBuildingRepository(): BuildingRepository
    {
        return $this->getRepository(Building::class);
    }

    public function getMyTeamMemberRepository(): MemberRepository
    {
        return $this->getRepository(Member::class);
    }

    public function getDelegatedAccessRepository(): DelegatedAccessRepository
    {
        return $this->getRepository(DelegatedAccess::class);
    }

    public function getFranceCities(): ?FranceCities
    {
        return $this->get(FranceCities::class);
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->get(EventDispatcherInterface::class);
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
     * @param string|null $email email used to generate a unique UUID
     */
    protected function createAdherent(?string $email = null): Adherent
    {
        $email = $email ?: 'john.smith@example.org';
        $phone = new PhoneNumber();
        $phone->setCountryCode('FR');
        $phone->setNationalNumber('0140998211');

        return Adherent::create(
            Adherent::createUuid($email),
            'ABC-234',
            $email,
            'super-password',
            'male',
            'John',
            'Smith',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone
        );
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

    protected function getEventCategoryIdForName(string $categoryName): int
    {
        return $this->manager->getRepository(EventCategory::class)->findOneBy(['name' => $categoryName])->getId();
    }

    protected function createPostAddress(
        string $street,
        string $cityCode,
        ?string $region = null,
        ?float $latitude = null,
        ?float $longitude = null,
        bool $nullable = false,
    ): AddressInterface {
        [, $inseeCode] = explode('-', $cityCode);
        $city = $this->getFranceCities()->getCityByInseeCode($inseeCode);

        if ($nullable) {
            return NullablePostAddress::createFrenchAddress($street, $cityCode, $city?->getName(), null, $region, $latitude, $longitude);
        }

        return PostAddress::createFrenchAddress($street, $cityCode, $city?->getName(), null, $region, $latitude, $longitude);
    }
}

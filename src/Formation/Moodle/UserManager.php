<?php

declare(strict_types=1);

namespace App\Formation\Moodle;

use App\Entity\Adherent;
use App\Entity\Moodle\User;
use App\Entity\Moodle\UserJob;
use App\Repository\AdherentRepository;
use App\Repository\Moodle\MoodleUserRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class UserManager
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly MoodleUserRepository $moodleUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Driver $driver,
        LoggerInterface $logger,
    ) {
        $this->logger = $logger;
    }

    public function updateUser(string $userUuid): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($userUuid)) {
            return;
        }

        if (empty($userData = $this->driver->findUserByEmail($adherent->getEmailAddress()))) {
            $userData = $this->driver->createUser([
                'email' => $email = $adherent->getEmailAddress(),
                'username' => $email,
                'firstname' => $adherent->getFirstName(),
                'lastname' => $adherent->getLastName(),
                'auth' => 'oauth2',
            ]);
        }

        if (empty($userData['id'])) {
            $this->logger->error('Moodle user data is missing ID', ['userData' => $userData, 'adherentId' => $adherent->getId()]);

            return;
        }

        if (!$moodleUser = $this->moodleUserRepository->findOneBy(['moodleId' => $userData['id']])) {
            $this->entityManager->persist($moodleUser = new User($adherent, $userData['id']));
            $this->entityManager->flush();
        }

        $adherentJobs = $this->prepareAdherentJobs($adherent);

        foreach ($moodleUser->getJobs() as $moodleJob) {
            if (isset($adherentJobs[$moodleJob->jobKey])) {
                unset($adherentJobs[$moodleJob->jobKey]);
                continue;
            }

            $this->driver->removeJob($moodleJob->moodleId);
            $moodleUser->removeJob($moodleJob);
            $this->entityManager->flush();
        }

        foreach ($adherentJobs as $key => $jobData) {
            if ($jobId = $this->driver->createJob($moodleUser->moodleId, $jobData)) {
                $moodleUser->addJob(new UserJob($moodleUser, $jobId, $jobData['jobdepartment'], $jobData['jobposition'], $key));
                $this->entityManager->flush();
            }
        }
    }

    private function prepareAdherentJobs(Adherent $adherent): array
    {
        $jobs = [];
        $startData = new \DateTime('2022-01-01 00:00:00')->getTimestamp();

        if ($adherent->isRenaissanceAdherent()) {
            $endDate = $assemblyCode = null;

            if ($assembly = $adherent->getAssemblyZone()) {
                $assemblyCode = $assembly->getCode();
            } else {
                $this->logger->error('Assembly zone is missing for adherent', ['adherentId' => $adherent->getId()]);
            }

            $year = (int) date('Y');
            if (!$adherent->hasActiveMembership()) {
                $year = $adherent->getLastMembershipDonation()?->format('Y') ?? $adherent->getFirstMembershipDonation()?->format('Y') ?? 2022;
                $endDate = new \DateTime()->setDate((int) $year, 12, 31)->setTime(23, 59, 59)->getTimestamp();
            }

            $zones = [
                $assemblyCode ? 'departement:'.$assemblyCode : null,
                $adherent->getCommitteeMembership()?->getCommitteeUuid()->toString(),
            ];

            foreach (array_filter($zones) as $zone) {
                $key = [
                    $zone,
                    $position = 'adherent',
                    $year,
                ];

                $jobs[implode('-', $key)] = [
                    'jobdepartment' => $zone,
                    'jobposition' => $position,
                    'startdate' => $startData,
                    'enddate' => $endDate,
                ];
            }
        }

        foreach ($adherent->getZoneBasedRoles() as $role) {
            if (!\in_array($role->getType(), [ScopeEnum::DEPUTY, ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, ScopeEnum::MUNICIPAL_CANDIDATE], true)) {
                continue;
            }

            if (!$assemblyZone = $role->getAssemblyZone()) {
                $this->logger->error('Assembly zone is missing for adherent role', ['adherentId' => $adherent->getId(), 'roleType' => $role->getType()]);
                continue;
            }

            $key = [
                $department = 'departement:'.$assemblyZone->getCode(),
                $position = $role->getType(),
            ];

            $jobs[implode('-', $key)] = [
                'jobdepartment' => $department,
                'jobposition' => $position,
                'startdate' => $startData,
            ];
        }

        foreach ($adherent->getAnimatorCommittees() as $committee) {
            $zone = $committee->getUuidAsString();

            $key = [
                $zone,
                $position = ScopeEnum::ANIMATOR,
            ];

            $jobs[implode('-', $key)] = [
                'jobdepartment' => $zone,
                'jobposition' => $position,
                'startdate' => $startData,
            ];
        }

        foreach ($adherent->getReceivedDelegatedAccesses() as $access) {
            if (!\in_array($access->getType(), [ScopeEnum::DEPUTY, ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, ScopeEnum::ANIMATOR, ScopeEnum::MUNICIPAL_CANDIDATE], true)) {
                continue;
            }

            $zone = null;

            if (ScopeEnum::ANIMATOR === $access->getType()) {
                if ($committees = $access->getDelegator()?->getAnimatorCommittees()) {
                    $zone = $committees[0]->getUuidAsString();
                }
            } else {
                if ($assemblyZone = $access->getDelegator()?->findZoneBasedRole($access->getType())?->getAssemblyZone()) {
                    $zone = 'departement:'.$assemblyZone->getCode();
                }
            }

            if (!$zone) {
                $this->logger->error('Assembly zone is missing for adherent role', ['adherentId' => $access->getDelegator()->getId(), 'roleType' => $access->getType()]);
                continue;
            }

            $key = [
                $zone,
                $position = 'team:'.$access->getType(),
            ];

            $jobs[implode('-', $key)] = [
                'jobdepartment' => $zone,
                'jobposition' => $position,
                'startdate' => $startData,
            ];
        }

        return $jobs;
    }
}

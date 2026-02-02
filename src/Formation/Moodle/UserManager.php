<?php

declare(strict_types=1);

namespace App\Formation\Moodle;

use App\Entity\Adherent;
use App\Entity\AdherentStaticLabel;
use App\Entity\Moodle\User;
use App\Entity\Moodle\UserJob;
use App\Repository\AdherentRepository;
use App\Repository\Moodle\MoodleUserRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

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
            $jobId = $this->driver->createJob($moodleUser->moodleId, $jobData['request']);

            if (
                null === $jobId
                && !empty($jobData['zone']['code'])
                && Uuid::isValid($jobData['zone']['code'])
                && $this->driver->createDepartment(
                    $jobData['zone']['name'] ?? $jobData['zone']['code'],
                    $jobData['zone']['code'],
                    $jobData['zone']['parent']
                )
            ) {
                $jobId = $this->driver->createJob($moodleUser->moodleId, $jobData['request']);
            }

            if ($jobId) {
                $moodleUser->addJob(new UserJob($moodleUser, $jobId, $jobData['request']['jobdepartment'], $jobData['request']['jobposition'], $key));
                $this->entityManager->flush();
            }
        }
    }

    private function prepareAdherentJobs(Adherent $adherent): array
    {
        $jobs = [];
        $startData = new \DateTime('2022-01-01 00:00:00')->getTimestamp();
        $adherentAssembly = null;

        if ($assembly = $adherent->getAssemblyZone()) {
            $adherentAssembly = 'departement:'.$assembly->getCode();
        } else {
            $this->logger->error('Assembly zone is missing for adherent', ['adherentId' => $adherent->getId()]);
        }

        if ($adherent->isRenaissanceAdherent()) {
            $endDate = null;
            $year = (int) date('Y');
            if (!$adherent->hasActiveMembership()) {
                $year = $adherent->getLastMembershipDonation()?->format('Y') ?? $adherent->getFirstMembershipDonation()?->format('Y') ?? 2022;
                $endDate = new \DateTime()->setDate((int) $year, 12, 31)->setTime(23, 59, 59)->getTimestamp();
            }

            $zones = [
                $adherentAssembly,
                ($committeeMembership = $adherent->getCommitteeMembership()) ? [
                    'code' => ($committee = $committeeMembership->getCommittee())->getUuidAsString(),
                    'name' => $committee->getName(),
                    'parent' => ($committeeAssemblyZone = $committee->getAssemblyZone()) ? 'departement:'.$committeeAssemblyZone->getCode() : null,
                ] : null,
            ];

            foreach (array_filter($zones) as $zone) {
                $key = [
                    $zone['code'],
                    $position = 'adherent',
                    $year,
                ];

                $jobs[implode('-', $key)] = [
                    'request' => [
                        'jobdepartment' => $zone['code'],
                        'jobposition' => $position,
                        'startdate' => $startData,
                        'enddate' => $endDate,
                    ],
                    'zone' => $zone,
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
                'request' => [
                    'jobdepartment' => $department,
                    'jobposition' => $position,
                    'startdate' => $startData,
                ],
            ];
        }

        foreach ($adherent->getAnimatorCommittees() as $committee) {
            $zone = [
                'code' => $committee->getUuidAsString(),
                'name' => $committee->getName(),
                'parent' => ($committeeAssemblyZone = $committee->getAssemblyZone()) ? 'departement:'.$committeeAssemblyZone->getCode() : null,
            ];

            $key = [
                $zone['code'],
                $position = ScopeEnum::ANIMATOR,
            ];

            $jobs[implode('-', $key)] = [
                'request' => [
                    'jobdepartment' => $zone['code'],
                    'jobposition' => $position,
                    'startdate' => $startData,
                ],
                'zone' => $zone,
            ];
        }

        foreach ($adherent->getReceivedDelegatedAccesses() as $access) {
            if (!\in_array($access->getType(), [ScopeEnum::DEPUTY, ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, ScopeEnum::ANIMATOR, ScopeEnum::MUNICIPAL_CANDIDATE], true)) {
                continue;
            }

            $zone = null;

            if (ScopeEnum::ANIMATOR === $access->getType()) {
                if ($committees = $access->getDelegator()?->getAnimatorCommittees()) {
                    $zone = [
                        'code' => $committees[0]->getUuidAsString(),
                        'name' => $committees[0]->getName(),
                        'parent' => ($committeeAssemblyZone = $committees[0]->getAssemblyZone()) ? 'departement:'.$committeeAssemblyZone->getCode() : null,
                    ];
                }
            } else {
                if ($assemblyZone = $access->getDelegator()?->findZoneBasedRole($access->getType())?->getAssemblyZone()) {
                    $zone = ['code' => 'departement:'.$assemblyZone->getCode()];
                }
            }

            if (!$zone) {
                $this->logger->error('Assembly zone is missing for adherent role', ['adherentId' => $access->getDelegator()->getId(), 'roleType' => $access->getType()]);
                continue;
            }

            $key = [
                $zone['code'],
                $position = 'team:'.$access->getType(),
            ];

            $jobs[implode('-', $key)] = [
                'request' => [
                    'jobdepartment' => $zone['code'],
                    'jobposition' => $position,
                    'startdate' => $startData,
                ],
                'zone' => $zone,
            ];
        }

        foreach ($adherent->getStaticLabels()->filter(fn (AdherentStaticLabel $label) => \in_array($label->type, ['pilote', 'espoir'])) as $label) {
            $key = array_filter([
                $adherentAssembly,
                $label->type,
            ]);

            $jobs[implode('-', $key)] = [
                'request' => [
                    'jobdepartment' => $adherentAssembly,
                    'jobposition' => $label->type,
                    'startdate' => $startData,
                ],
                'zone' => [
                    'code' => $adherentAssembly,
                ],
            ];
        }

        return $jobs;
    }
}

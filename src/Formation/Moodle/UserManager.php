<?php

declare(strict_types=1);

namespace App\Formation\Moodle;

use App\Entity\Adherent;
use App\Entity\AdherentStaticLabel;
use App\Entity\AgoraMembership;
use App\Entity\Moodle\User;
use App\Entity\Moodle\UserJob;
use App\Repository\AdherentRepository;
use App\Repository\Moodle\MoodleUserRepository;
use App\Scope\ScopeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class UserManager
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly MoodleUserRepository $moodleUserRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Driver $driver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function updateUser(string $userUuid): void
    {
        if (!$adherent = $this->adherentRepository->findOneByUuid($userUuid)) {
            return;
        }

        $email = $adherent->getEmailAddress();
        $syncedFields = $this->getSyncedFields($adherent);

        if (!$moodleUser = $this->moodleUserRepository->findOneBy(['adherent' => $adherent])) {
            if (empty($moodleId = $this->findOrCreateMoodleUser($adherent, $email, $syncedFields)['id'] ?? null)) {
                $this->logger->error('Moodle user data is missing ID', ['adherentId' => $adherent->getId()]);

                return;
            }

            $this->entityManager->persist($moodleUser = new User($adherent, $moodleId));
            $this->entityManager->flush();
        } else {
            // The Moodle account is identified by the stored moodleId, not by the (mutable) email.
            // Push any email change to Moodle so the user keeps their account — and therefore their
            // progress — instead of being matched to a brand-new account on the next OAuth login.
            $userData = $this->driver->findUserById($moodleUser->moodleId);

            if (empty($userData)) {
                // Account no longer exists on Moodle: re-provision and repoint the local link.
                if (empty($moodleId = $this->findOrCreateMoodleUser($adherent, $email, $syncedFields)['id'] ?? null)) {
                    $this->logger->error('Moodle user data is missing ID', ['adherentId' => $adherent->getId()]);

                    return;
                }

                $moodleUser->moodleId = $moodleId;
                $this->entityManager->flush();
            } else {
                $update = [];

                if (($userData['email'] ?? null) !== $email) {
                    $update['email'] = $email;
                    $update['username'] = $email;
                }

                foreach ($syncedFields as $key => $value) {
                    if (($userData[$key] ?? null) !== $value) {
                        $update[$key] = $value;
                    }
                }

                if ($update) {
                    $this->driver->updateUser($moodleUser->moodleId, $update);
                }
            }
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

    private function findOrCreateMoodleUser(Adherent $adherent, string $email, array $syncedFields): array
    {
        if (!empty($userData = $this->driver->findUserByEmail($email))) {
            return $userData;
        }

        return $this->driver->createUser([
            'email' => $email,
            'username' => $email,
            'auth' => 'oauth2',
            ...$syncedFields,
        ]);
    }

    /**
     * Adherent-derived Moodle user fields kept in sync on both create and update.
     *
     * @return array<string, string>
     */
    public function getSyncedFields(Adherent $adherent): array
    {
        return array_filter([
            'firstname' => $adherent->getFirstName(),
            'lastname' => $adherent->getLastName(),
            'country' => ($country = $adherent->getCountry()) ? strtoupper($country) : null,
            'city' => $adherent->getCityName(),
            'department' => ($zone = $adherent->getAssemblyZone()) ? \sprintf('%s (%s)', $zone->getName(), $zone->getCode()) : null,
        ], static fn (?string $value) => null !== $value && '' !== $value);
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
            $adherentStartDate = $adherent->getFirstMembershipDonation()?->getTimestamp() ?? $startData;
            $endDate = null;
            $year = (int) date('Y');
            if (!$adherent->hasActiveMembership()) {
                $year = $adherent->getLastMembershipDonation()?->format('Y') ?? $adherent->getFirstMembershipDonation()?->format('Y') ?? 2022;
                $endDate = new \DateTime()->setDate((int) $year, 12, 31)->setTime(23, 59, 59)->getTimestamp();
            }

            $zones = [
                $adherentAssembly ? ['code' => $adherentAssembly] : null,
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
                    $adherentStartDate,
                    $endDate,
                ];

                $jobs[implode('-', $key)] = [
                    'request' => [
                        'jobdepartment' => $zone['code'],
                        'jobposition' => $position,
                        'startdate' => $adherentStartDate,
                        'enddate' => $endDate,
                    ],
                    'zone' => $zone,
                ];
            }

            foreach ($adherent->findElectedRepresentativeMandates(true) as $mandate) {
                if (!$mandateAssembly = $mandate->zone?->getAssemblyZone()) {
                    continue;
                }

                $zone = ['code' => 'departement:'.$mandateAssembly->getCode()];
                $key = [
                    $zone['code'],
                    $position = 'elu',
                    $adherentStartDate,
                ];

                $jobs[implode('-', $key)] = [
                    'request' => [
                        'jobdepartment' => $zone['code'],
                        'jobposition' => $position,
                        'startdate' => $adherentStartDate,
                    ],
                    'zone' => $zone,
                ];
            }
        }

        /** @var AgoraMembership $membership */
        foreach ($adherent->agoraMemberships as $membership) {
            $key = [
                $department = 'agora:'.$membership->agora->getId(),
                $position = 'adherent',
            ];

            $jobs[implode('-', $key)] = [
                'request' => [
                    'jobdepartment' => $department,
                    'jobposition' => $position,
                    'startdate' => $startData,
                ],
            ];
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

            $this->logger->error('Adding static label job for adherent', ['adherentId' => $adherent->getId(), 'labelType' => $label->type]);

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

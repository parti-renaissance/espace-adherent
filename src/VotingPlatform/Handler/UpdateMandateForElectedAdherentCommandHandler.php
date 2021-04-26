<?php

namespace App\VotingPlatform\Handler;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\VotingPlatform\Candidate;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\Command\UpdateMandateForElectedAdherentCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateMandateForElectedAdherentCommandHandler implements MessageHandlerInterface
{
    private $entityManager;
    private $electionRepository;

    public function __construct(EntityManagerInterface $entityManager, ElectionRepository $electionRepository)
    {
        $this->entityManager = $entityManager;
        $this->electionRepository = $electionRepository;
    }

    public function __invoke(UpdateMandateForElectedAdherentCommand $command): void
    {
        $election = $this->electionRepository->findOneByUuid($command->getElectionUuid()->toString());

        if (!$election) {
            return;
        }

        // Skip National council election
        if (DesignationTypeEnum::NATIONAL_COUNCIL === $election->getDesignationType()) {
            return;
        }

        $this->closeExistingMandates($election);

        $this->openNewMandates($election);
    }

    private function closeExistingMandates(Election $election): void
    {
        $designation = $election->getDesignation();

        switch ($election->getDesignationType()) {
            case DesignationTypeEnum::COMMITTEE_ADHERENT:
            case DesignationTypeEnum::COMMITTEE_SUPERVISOR:
                // do not close any committee mandate if an election has 0 elected candidate
                if (!$election->hasElected()) {
                    return;
                }

                $repository = $this->entityManager->getRepository(CommitteeAdherentMandate::class);

                $repository->closeCommitteeMandate(
                    $election->getElectionEntity()->getCommittee(),
                    AbstractAdherentMandate::REASON_ELECTION,
                    $election->getVoteEndDate(),
                    DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType() ?
                        CommitteeAdherentMandateTypeEnum::TYPE_SUPERVISOR :
                        CommitteeAdherentMandateTypeEnum::TYPE_DESIGNED_ADHERENT,
                    $designation->getPools() ? current($designation->getPools()) : null
                );

                break;

            case DesignationTypeEnum::COPOL:
                $repository = $this->entityManager->getRepository(TerritorialCouncilAdherentMandate::class);

                $qualities = $election->getDesignation()->getPoolTypes();

                $repository->closeTerritorialCouncilMandate(
                    $election->getElectionEntity()->getTerritorialCouncil(),
                    $election->getVoteEndDate(),
                    $qualities
                );

                break;
        }
    }

    private function openNewMandates(Election $election): void
    {
        $result = $election->getElectionResult();

        $electedPoolResults = $result->getElectedPoolResults();

        $mandateFactory = DesignationTypeEnum::COPOL === $election->getDesignationType() ?
            function (Candidate $candidate, Election $election, string $quality, bool $additionallyElected): TerritorialCouncilAdherentMandate {
                return new TerritorialCouncilAdherentMandate(
                    $candidate->getAdherent(),
                    $election->getElectionEntity()->getTerritorialCouncil(),
                    $quality,
                    $candidate->getGender(),
                    $election->getVoteEndDate(),
                    null,
                    $additionallyElected
                );
            }
        :
            function (Candidate $candidate, Election $election): CommitteeAdherentMandate {
                return new CommitteeAdherentMandate(
                    $candidate->getAdherent(),
                    $candidate->getGender(),
                    $election->getElectionEntity()->getCommittee(),
                    $election->getVoteEndDate(),
                    DesignationTypeEnum::COMMITTEE_SUPERVISOR === $election->getDesignationType() ? CommitteeAdherentMandateTypeEnum::TYPE_SUPERVISOR : null
                );
            }
        ;

        $candidates = [];

        foreach ($electedPoolResults as $poolResult) {
            foreach ($poolResult->getElectedCandidateGroups() as $candidateGroup) {
                foreach ($candidateGroup->getCandidates() as $candidate) {
                    array_push($candidates, [
                        'candidate' => $candidate,
                        'quality' => $poolResult->getElectionPool()->getCode(),
                    ]);
                }
            }
        }

        if (DesignationTypeEnum::COPOL === $election->getDesignationType()) {
            foreach ($electedPoolResults as $poolResult) {
                foreach ($poolResult->getAdditionallyElectedCandidates() as $candidate) {
                    array_push($candidates, [
                        'candidate' => $candidate,
                        'quality' => $poolResult->getElectionPool()->getCode(),
                        'additionally_elected' => true,
                    ]);
                }
            }
        }

        array_map(function (array $row) use ($mandateFactory, $election) {
            $mandate = $mandateFactory($row['candidate'], $election, $row['quality'], !empty($row['additionally_elected']));
            $this->entityManager->persist($mandate);
        }, $candidates);

        $this->entityManager->flush();
    }
}

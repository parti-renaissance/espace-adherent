<?php

namespace App\VotingPlatform\Handler;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\NationalCouncilAdherentMandate;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\AdherentMandate\AdherentMandateFactory;
use App\VotingPlatform\Command\UpdateMandateForElectedAdherentCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateMandateForElectedAdherentCommandHandler implements MessageHandlerInterface
{
    private $mandateFactory;
    private $entityManager;
    private $electionRepository;

    public function __construct(
        AdherentMandateFactory $mandateFactory,
        EntityManagerInterface $entityManager,
        ElectionRepository $electionRepository
    ) {
        $this->mandateFactory = $mandateFactory;
        $this->entityManager = $entityManager;
        $this->electionRepository = $electionRepository;
    }

    public function __invoke(UpdateMandateForElectedAdherentCommand $command): void
    {
        $election = $this->electionRepository->findOneByUuid($command->getElectionUuid()->toString());

        if (!$election) {
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
                // do not close any committee mandate if an election has 0 elected candidate
                if (!$election->hasElected()) {
                    return;
                }

                $repository = $this->entityManager->getRepository(CommitteeAdherentMandate::class);

                $repository->closeMandates(
                    $election->getElectionEntity()->getCommittee(),
                    AdherentMandateInterface::REASON_ELECTION,
                    $election->getVoteEndDate(),
                    CommitteeAdherentMandateTypeEnum::TYPE_DESIGNED_ADHERENT,
                    $designation->getPools() ? current($designation->getPools()) : null
                );

                break;

            case DesignationTypeEnum::COPOL:
                $repository = $this->entityManager->getRepository(TerritorialCouncilAdherentMandate::class);

                $qualities = $election->getDesignation()->getPoolTypes();

                $repository->closeMandates(
                    $election->getElectionEntity()->getTerritorialCouncil(),
                    $election->getVoteEndDate(),
                    $qualities
                );

                break;

            case DesignationTypeEnum::NATIONAL_COUNCIL:
                $repository = $this->entityManager->getRepository(NationalCouncilAdherentMandate::class);

                $repository->closeMandates(
                    $election->getElectionEntity()->getTerritorialCouncil(),
                    $election->getVoteEndDate()
                );

                break;
        }
    }

    private function openNewMandates(Election $election): void
    {
        $result = $election->getElectionResult();
        $electedPoolResults = $result->getElectedPoolResults();
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

        array_map(function (array $row) use ($election) {
            $this->entityManager->persist($mandate = $this->mandateFactory->create($election, $row['candidate'], $row['quality']));
            if (!empty($row['additionally_elected']) && $mandate instanceof TerritorialCouncilAdherentMandate) {
                $mandate->setIsAdditionallyElected(true);
            }
        }, $candidates);

        $this->entityManager->flush();
    }
}

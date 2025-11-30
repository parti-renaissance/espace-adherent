<?php

declare(strict_types=1);

namespace App\VotingPlatform\Handler;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\VotingPlatform\Election;
use App\Repository\VotingPlatform\ElectionRepository;
use App\VotingPlatform\AdherentMandate\AdherentMandateFactory;
use App\VotingPlatform\Command\UpdateMandateForElectedAdherentCommand;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateMandateForElectedAdherentCommandHandler
{
    private $mandateFactory;
    private $entityManager;
    private $electionRepository;

    public function __construct(
        AdherentMandateFactory $mandateFactory,
        EntityManagerInterface $entityManager,
        ElectionRepository $electionRepository,
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
                    $candidates[] = [
                        'candidate' => $candidate,
                        'quality' => $poolResult->getElectionPool()->getCode(),
                    ];
                }
            }
        }

        if (DesignationTypeEnum::COPOL === $election->getDesignationType()) {
            foreach ($electedPoolResults as $poolResult) {
                foreach ($poolResult->getAdditionallyElectedCandidates() as $candidate) {
                    $candidates[] = [
                        'candidate' => $candidate,
                        'quality' => $poolResult->getElectionPool()->getCode(),
                        'additionally_elected' => true,
                    ];
                }
            }
        }

        array_map(function (array $row) use ($election) {
            $this->entityManager->persist($this->mandateFactory->create($election, $row['candidate'], $row['quality']));
        }, $candidates);

        $this->entityManager->flush();
    }
}

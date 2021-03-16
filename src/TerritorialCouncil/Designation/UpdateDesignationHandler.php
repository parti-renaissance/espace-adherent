<?php

namespace App\TerritorialCouncil\Designation;

use App\Address\PostAddressFactory;
use App\Entity\TerritorialCouncil\Election;
use App\Entity\VotingPlatform\Designation\Designation;
use App\TerritorialCouncil\Event\TerritorialCouncilEvent;
use App\TerritorialCouncil\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class UpdateDesignationHandler
{
    private $entityManager;
    private $eventDispatcher;
    private $postAddressFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostAddressFactory $postAddressFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->postAddressFactory = $postAddressFactory;
        $this->eventDispatcher = $dispatcher;
    }

    public function __invoke(UpdateDesignationRequest $request, Election $election): void
    {
        $coTerr = $election->getTerritorialCouncil();

        // persist new designation
        $this->entityManager->persist($newDesignation = $this->createDesignationFromRequest($request, $election));

        $election->setMeetingStartDate($request->getMeetingStartDate());
        $election->setMeetingEndDate($request->getMeetingEndDate());
        $election->setDescription($request->getDescription());
        $election->setQuestions($request->getQuestions());
        $election->setElectionMode($request->getVoteMode());

        if ($election->isOnlineMode()) {
            $election->setMeetingUrl($request->getMeetingUrl());
        } else {
            $election->updatePostAddress($this->postAddressFactory->createFromAddress($request->getAddress()));
        }

        $this->entityManager->flush();

        // link new designation with Election and CoTerr
        $election->setDesignation($newDesignation);
        $coTerr->setCurrentDesignation($newDesignation);

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new TerritorialCouncilEvent($coTerr), Events::DESIGNATION_SWITCHED);
    }

    private function createDesignationFromRequest(UpdateDesignationRequest $request, Election $election): Designation
    {
        $coTerr = $election->getTerritorialCouncil();

        $coTerrRefTags = $coTerr->getReferentTags()->toArray();
        $designationRefTags = $election->getDesignation()->getReferentTags();

        // Clone designation if it has more referent tags that the current territorial council, otherwise use the same designation
        if (\count(array_intersect($designationRefTags, $coTerrRefTags)) !== \count($designationRefTags)) {
            $designation = clone $election->getDesignation();
        } else {
            $designation = $election->getDesignation();
        }

        $designation->setLabel($coTerr->getName().' '.$request->getVoteStartDate()->format('d-m-Y'));
        $designation->setReferentTags($coTerrRefTags);
        $designation->setCandidacyEndDate((clone $request->getVoteStartDate())->modify('-48 hours'));
        $designation->setVoteStartDate($request->getVoteStartDate());
        $designation->setVoteEndDate($request->getVoteEndDate());
        $designation->setCreatedAt($now = new \DateTime());
        $designation->setUpdatedAt($now);
        $designation->markAsLimited();

        return $designation;
    }
}

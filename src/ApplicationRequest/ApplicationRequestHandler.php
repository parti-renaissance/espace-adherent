<?php

namespace App\ApplicationRequest;

use App\Entity\ApplicationRequest\ApplicationRequest;
use App\Entity\ApplicationRequest\RunningMateRequest;
use App\Entity\ApplicationRequest\VolunteerRequest;
use App\Referent\ReferentTagManager;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApplicationRequestHandler
{
    private $manager;
    private $storage;
    private $eventDispatcher;
    private $adherentRepository;
    private $referentTagManager;

    public function __construct(
        AdherentRepository $adherentRepository,
        EntityManagerInterface $manager,
        Filesystem $storage,
        EventDispatcherInterface $eventDispatcher,
        ReferentTagManager $referentTagManager
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->manager = $manager;
        $this->storage = $storage;
        $this->eventDispatcher = $eventDispatcher;
        $this->referentTagManager = $referentTagManager;
    }

    public function handleVolunteerRequest(VolunteerRequest $volunteerRequest): void
    {
        $this->addAdherentRelation($volunteerRequest);
        $this->assignReferentTags($volunteerRequest);

        $this->manager->persist($volunteerRequest);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(Events::CREATED, new ApplicationRequestEvent($volunteerRequest));
    }

    public function handleRunningMateRequest(RunningMateRequest $runningMateRequest): void
    {
        $this->addAdherentRelation($runningMateRequest);
        $this->assignReferentTags($runningMateRequest);

        $this->uploadCurriculum($runningMateRequest);

        $this->manager->persist($runningMateRequest);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(Events::CREATED, new ApplicationRequestEvent($runningMateRequest));
    }

    public function uploadCurriculum(RunningMateRequest $runningMateRequest): void
    {
        if (!$runningMateRequest->getCurriculum() instanceof UploadedFile) {
            throw new \RuntimeException(sprintf('The file must be an instance of %s', UploadedFile::class));
        }

        $runningMateRequest->setCurriculumNameFromUploadedFile($runningMateRequest->getCurriculum());
        $path = $runningMateRequest->getPathWithDirectory();

        $this->storage->put($path, file_get_contents($runningMateRequest->getCurriculum()->getPathname()));
    }

    private function addAdherentRelation(ApplicationRequest $applicationRequest): void
    {
        $applicationRequest->setAdherent(
            $this->adherentRepository->findOneByEmail($applicationRequest->getEmailAddress())
        );
    }

    private function assignReferentTags(ApplicationRequest $applicationRequest): void
    {
        $this->referentTagManager->assignApplicationRequestReferentTags($applicationRequest);
    }
}

<?php

namespace AppBundle\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApplicationRequestHandler
{
    private $manager;
    private $storage;

    public function __construct(EntityManagerInterface $manager, Filesystem $storage)
    {
        $this->manager = $manager;
        $this->storage = $storage;
    }

    public function handleVolunteerRequest(VolunteerRequest $volunteerRequest): void
    {
        $this->manager->persist($volunteerRequest);
        $this->manager->flush();

        // TODO: send email to confirm volunteer request
    }

    public function handleRunningMateRequest(RunningMateRequest $runningMateRequest): void
    {
        $this->uploadCurriculum($runningMateRequest);

        $this->manager->persist($runningMateRequest);
        $this->manager->flush();

        // TODO: send email to confirm running mate request
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
}

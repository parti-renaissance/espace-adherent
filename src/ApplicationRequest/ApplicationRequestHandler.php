<?php

namespace AppBundle\ApplicationRequest;

use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\ApplicationRequestConfirmationMessage;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApplicationRequestHandler
{
    private $manager;
    private $storage;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        EntityManagerInterface $manager,
        Filesystem $storage,
        MailerService $mailer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->manager = $manager;
        $this->storage = $storage;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function handleVolunteerRequest(VolunteerRequest $volunteerRequest): void
    {
        $this->manager->persist($volunteerRequest);
        $this->manager->flush();

        $this->sendConfirmationEmail($volunteerRequest);
    }

    public function handleRunningMateRequest(RunningMateRequest $runningMateRequest): void
    {
        $this->uploadCurriculum($runningMateRequest);

        $this->manager->persist($runningMateRequest);
        $this->manager->flush();

        $this->sendConfirmationEmail($runningMateRequest);
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

    private function sendConfirmationEmail(ApplicationRequest $applicationRequest): void
    {
        $this->mailer->sendMessage(ApplicationRequestConfirmationMessage::create(
            $applicationRequest,
            $this->urlGenerator->generate('app_application_request_request', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}

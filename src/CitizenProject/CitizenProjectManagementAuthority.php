<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenProjectManagementAuthority
{
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        CitizenProjectManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailerService $mailer
    ) {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
    }

    public function approve(CitizenProject $citizenProject): void
    {
        $this->manager->approveCitizenProject($citizenProject);

        $this->mailer->sendMessage(CitizenProjectApprovalConfirmationMessage::create(
            $this->manager->getCitizenProjectCreator($citizenProject),
            $citizenProject->getCityName(),
            $this->urlGenerator->generate('app_citizen_project_show', ['slug' => $citizenProject->getSlug()])
        ));
    }

    public function refuse(CitizenProject $citizenProject): void
    {
        $this->manager->refuseCitizenProject($citizenProject);
    }
}

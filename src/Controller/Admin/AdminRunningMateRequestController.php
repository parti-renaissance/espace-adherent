<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class AdminRunningMateRequestController extends CRUDController
{
    public function curriculumAction(Filesystem $privateStorage): Response
    {
        /** @var RunningMateRequest $runningMateRequest */
        $runningMateRequest = $this->admin->getSubject();

        return new Response($privateStorage->read($runningMateRequest->getPathWithDirectory()), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}

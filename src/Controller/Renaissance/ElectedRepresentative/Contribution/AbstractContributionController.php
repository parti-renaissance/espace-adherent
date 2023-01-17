<?php

namespace App\Controller\Renaissance\ElectedRepresentative\Contribution;

use App\ElectedRepresentative\Contribution\ContributionRequest;
use App\ElectedRepresentative\Contribution\ContributionRequestProcessor;
use App\ElectedRepresentative\Contribution\ContributionRequestStorage;
use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractContributionController extends AbstractController
{
    public function __construct(
        protected readonly ContributionRequestStorage $storage,
        protected readonly ContributionRequestProcessor $processor
    ) {
    }

    public function checkContributionsEnabled(): void
    {
        if ('production' === $this->getParameter('app_environment')) {
            throw $this->createNotFoundException('Contributions are disabled.');
        }
    }

    protected function getCommand(Request $request = null): ContributionRequest
    {
        /** @var ?Adherent $user */
        $user = $this->getUser();
        $command = $this->storage->getContributionRequest();

        if ($command->getAdherentId()) {
            if (!$user || $user->getId() !== $command->getAdherentId()) {
                $this->storage->clear();

                $command = new ContributionRequest();
            }
        } elseif ($user) {
            $command->updateFromAdherent($user);
        }

        return $command;
    }
}

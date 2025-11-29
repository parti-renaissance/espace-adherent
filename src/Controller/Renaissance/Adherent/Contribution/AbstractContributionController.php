<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Adherent\Contribution;

use App\Adherent\Contribution\ContributionRequest;
use App\Adherent\Contribution\ContributionRequestProcessor;
use App\Adherent\Contribution\ContributionRequestStorage;
use App\Entity\Adherent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractContributionController extends AbstractController
{
    public function __construct(
        protected readonly ContributionRequestStorage $storage,
        protected readonly ContributionRequestProcessor $processor,
    ) {
    }

    protected function getCommand(?Request $request = null): ContributionRequest
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

        if ($request && $request->query->has('redeclare')) {
            $command->setRedeclare(true);
        }

        return $command;
    }
}

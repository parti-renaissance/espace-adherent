<?php

namespace AppBundle\Adherent\Unregistration\Handlers;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentActivationTokenRepository;
use AppBundle\Repository\AdherentResetPasswordTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class RemoveAdherentTokensHandler implements UnregistrationAdherentHandlerInterface
{
    private $activationTokenRepository;
    private $resetPasswordTokenRepository;
    private $manager;

    public function __construct(
        EntityManagerInterface $manager,
        AdherentActivationTokenRepository $activationTokenRepository,
        AdherentResetPasswordTokenRepository $resetPasswordTokenRepository
    ) {
        $this->manager = $manager;
        $this->activationTokenRepository = $activationTokenRepository;
        $this->resetPasswordTokenRepository = $resetPasswordTokenRepository;
    }

    public function supports(Adherent $adherent): bool
    {
        return true;
    }

    public function handle(Adherent $adherent): void
    {
        $uuid = $adherent->getUuid()->toString();

        $this->remove($this->activationTokenRepository->findBy(['adherentUuid' => $uuid]));
        $this->remove($this->resetPasswordTokenRepository->findBy(['adherentUuid' => $uuid]));

        $this->manager->flush();
    }

    private function remove(array $tokens): void
    {
        array_walk($tokens, function ($entity) {
            $this->manager->remove($entity);
        });
    }
}

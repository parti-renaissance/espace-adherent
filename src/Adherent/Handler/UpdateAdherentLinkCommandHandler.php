<?php

declare(strict_types=1);

namespace App\Adherent\Handler;

use App\Adherent\Command\UpdateAdherentLinkCommand;
use App\Entity\Adherent;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\PetitionSignature;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Repository\UpdateAdherentLinkRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateAdherentLinkCommandHandler
{
    private const RESOURCES = [
        PetitionSignature::class,
        EventInscription::class,
        AdherentRequest::class,
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(UpdateAdherentLinkCommand $command): void
    {
        $repository = $this->getObjectRepository($command);

        if (!$object = $repository->findOneBy(['uuid' => $command->getUuid()])) {
            return;
        }

        if ($object instanceof Adherent) {
            $this->updateLinksWithNewAdherent($object);

            return;
        }

        if (method_exists($object, 'getAdherent') && $object->getAdherent()) {
            return;
        }

        if ($repository instanceof UpdateAdherentLinkRepositoryInterface) {
            $repository->updateAdherentLink($object);
            $this->entityManager->flush();
        }
    }

    private function getObjectRepository(UpdateAdherentLinkCommand $command): object
    {
        return $this->entityManager->getRepository($command->resourceClass);
    }

    private function updateLinksWithNewAdherent(Adherent $adherent): void
    {
        foreach (self::RESOURCES as $resourceClass) {
            $repository = $this->entityManager->getRepository($resourceClass);
            if ($repository instanceof UpdateAdherentLinkRepositoryInterface) {
                $repository->updateLinksWithNewAdherent($adherent);
            }
        }
    }
}

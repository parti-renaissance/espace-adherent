<?php

namespace App\Adherent\Certification\Handlers;

use App\Adherent\Certification\CertificationRequestBlockCommand;
use App\Entity\CertificationRequest;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;

class CertificationRequestDuplicateHandler implements CertificationRequestHandlerInterface
{
    private $em;
    private $adherentRepository;

    public function __construct(EntityManagerInterface $em, AdherentRepository $adherentRepository)
    {
        $this->em = $em;
        $this->adherentRepository = $adherentRepository;
    }

    public function supports(CertificationRequest $certificationRequest): bool
    {
        return $certificationRequest->isPending();
    }

    public function handle(CertificationRequest $certificationRequest): void
    {
        $adherent = $certificationRequest->getAdherent();

        $duplicateCertifiedAdherent = $this->adherentRepository->findCertified(
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getBirthdate(),
            $adherent
        );

        if (!$duplicateCertifiedAdherent) {
            return;
        }

        $certificationRequest->setFoundDuplicatedAdherent($duplicateCertifiedAdherent);
        $certificationRequest->block(CertificationRequestBlockCommand::BLOCK_REASON_MULTI_ACCOUNT);
        $certificationRequest->process();

        $this->em->flush();
    }
}

<?php

namespace AppBundle\Assessor\AssessorRole;

use AppBundle\Assessor\AssessorRoleAssociationValueObject;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRoleAssociation;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;

class AssessorAssociationManager
{
    private $adherentRepository;
    private $entityManager;

    public function __construct(AdherentRepository $adherentRepository, EntityManagerInterface $entityManager)
    {
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param VotePlace[] $votePlaces
     *
     * @return AssessorRoleAssociationValueObject[]
     */
    public function getAssociationValueObjectsFromVotePlaces(array $votePlaces): array
    {
        $assessors = $this->adherentRepository->findAssessorsForVotePlaces($votePlaces);

        $data = [];

        foreach ($votePlaces as $place) {
            $data[] = new AssessorRoleAssociationValueObject($place, $assessors[$place->getId()] ?? null);
        }

        return $data;
    }

    /**
     * @param AssessorRoleAssociationValueObject[] $valueObjects
     */
    public function handleUpdate(array $valueObjects): void
    {
        foreach ($valueObjects as $object) {
            /** @var Adherent|null $existingAdherent */
            $existingAdherent = current($this->adherentRepository->findAssessorsForVotePlaces([$object->getVotePlace()]));
            $roleToReuse = null;

            if ($adherent = $object->getAdherent()) {
                if ($existingAdherent) {
                    if ($existingAdherent->equals($adherent)) {
                        continue;
                    }

                    $roleToReuse = $existingAdherent->getAssessorRole();
                    $existingAdherent->setAssessorRole(null);
                }

                $adherent->setAssessorRole($roleToReuse ?? new AssessorRoleAssociation($object->getVotePlace()));
            } else {
                if (!$existingAdherent) {
                    continue;
                }

                $this->entityManager->remove($existingAdherent->getAssessorRole());
            }
        }

        $this->entityManager->flush();
    }
}

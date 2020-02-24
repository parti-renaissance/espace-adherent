<?php

namespace AppBundle\Validator;

use AppBundle\Assessor\AssessorRoleAssociationValueObject;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use AppBundle\Repository\AssessorRequestRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidAdherentForVotePlaceValidator extends ConstraintValidator
{
    private $assessorRepository;
    private $security;

    public function __construct(AssessorRequestRepository $assessorRepository, Security $security)
    {
        $this->assessorRepository = $assessorRepository;
        $this->security = $security;
    }

    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof ValidAdherentForVotePlace) {
            throw new UnexpectedTypeException($constraint, ValidAdherentForVotePlace::class);
        }

        if (!$object instanceof AssessorRoleAssociationValueObject) {
            throw new UnexpectedTypeException($object, AssessorRoleAssociationValueObject::class);
        }

        if (!$object->getAdherent()) {
            return;
        }

        /** @var AssessorRequest[] $assessorRequests */
        $assessorRequests = $this->assessorRepository->findBy([
            'emailAddress' => $object->getAdherent()->getEmailAddress(),
            'enabled' => true,
        ]);

        if (0 === \count($assessorRequests)) {
            $this->context
                ->buildViolation($constraint->messageCandidatureNotFound)
                ->atPath('adherent')
                ->addViolation()
            ;

            return;
        }

        /** @var Adherent $user */
        if (!$user = $this->security->getUser()) {
            return;
        }

        $isReferent = $user->isReferent();
        $isMunicipalChief = $user->isMunicipalChief();

        foreach ($assessorRequests as $assessorRequest) {
            if ($isReferent) {
                if ($this->matchReferentZone($assessorRequest, $user)) {
                    return;
                }
            } elseif ($isMunicipalChief) {
                if ($this->matchMunicipalChiefZone($assessorRequest, $user)) {
                    return;
                }
            }
        }

        $this->context
            ->buildViolation($constraint->messageWrongVotePlace)
            ->atPath('adherent')
            ->addViolation()
        ;
    }

    private function matchReferentZone(AssessorRequest $assessorRequest, Adherent $user): bool
    {
        return (bool) array_intersect(
            $user->getManagedAreaTagCodes(),
            [$assessorRequest->getAssessorCountry(), substr($assessorRequest->getAssessorPostalCode(), 0, 2)]
        );
    }

    private function matchMunicipalChiefZone(AssessorRequest $assessorRequest, Adherent $user): bool
    {
        return \in_array(
            $user->getMunicipalChiefManagedArea()->getCityName(),
            $assessorRequest->getVotePlaceWishes()->map(static function (VotePlace $votePlace) {
                return $votePlace->getCity();
            })->toArray(),
            true
        );
    }
}

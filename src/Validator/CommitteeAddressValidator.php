<?php

namespace App\Validator;

use App\Committee\CommitteeCommand;
use App\Committee\CommitteeCreationCommand;
use App\Entity\Committee;
use App\Geo\ZoneMatcher;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Repository\Geo\ZoneRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CommitteeAddressValidator extends ConstraintValidator
{
    private $committeeRepository;
    private $adherentRepository;
    private $zoneRepository;
    private $zoneMatcher;

    public function __construct(
        CommitteeRepository $committeeRepository,
        ZoneRepository $zoneRepository,
        ZoneMatcher $zoneMatcher,
        AdherentRepository $adherentRepository
    ) {
        $this->committeeRepository = $committeeRepository;
        $this->adherentRepository = $adherentRepository;
        $this->zoneMatcher = $zoneMatcher;
        $this->zoneRepository = $zoneRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof CommitteeAddress) {
            throw new UnexpectedTypeException($constraint, CommitteeAddress::class);
        }

        if (!$value instanceof CommitteeCommand) {
            throw new UnexpectedTypeException($value, CommitteeCommand::class);
        }

        $foundCommittee = $this->committeeRepository->findOneAcceptedByAddress($value->getAddress());
        $committee = $value->getCommittee();

        if ($foundCommittee
            && (null === $committee || ($committee instanceof Committee && !$committee->equals($foundCommittee)))) {
            $this
                ->context
                ->buildViolation($constraint->notUniqueAddressMessage)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }

        $adherent = null;
        if ($value instanceof CommitteeCreationCommand) {
            $adherent = $value->getAdherent();
        } elseif ($committee = $value->getCommittee()) {
            $adherent = $this->adherentRepository->findOneByUuid($committee->getCreator());
        }

        if (null === $adherent || !$adherent->isReferent()) {
            return;
        }

        $zones = $this->zoneMatcher->match($value->getAddress());
        if (0 === \count($zones) || !$this->zoneRepository->isInZones($zones, $adherent->getManagedArea()->getZones()->toArray())) {
            $this
                ->context
                ->buildViolation($constraint->notInZoneMessage)
                ->atPath($constraint->errorPath)
                ->addViolation()
            ;
        }
    }
}

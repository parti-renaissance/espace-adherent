<?php

namespace App\Validator;

use App\Entity\Pap\Campaign;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Pap\CampaignRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PapCampaignVotePlacesValidator extends ConstraintValidator
{
    private ScopeGeneratorResolver $scopeGeneratorResolver;
    private CampaignRepository $campaignRepository;
    private ZoneRepository $zoneRepository;

    public function __construct(
        ScopeGeneratorResolver $scopeGeneratorResolver,
        CampaignRepository $campaignRepository,
        ZoneRepository $zoneRepository
    ) {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->campaignRepository = $campaignRepository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @param Campaign $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof PapCampaignVotePlaces) {
            throw new UnexpectedTypeException($constraint, PapCampaignVotePlaces::class);
        }

        if (null === $value) {
            return;
        }

        $votePlaces = $value->getVotePlaces();
        if (0 === $votePlaces->count()) {
            return;
        }

        if ($this->campaignRepository->findCampaignsForVotePlaces($votePlaces->toArray(), $value->getId() ? $value : null)) {
            $this
                ->context
                ->buildViolation($constraint->messageAnotherCampaign)
                ->atPath('votePlaces')
                ->addViolation()
            ;
        }

        if (!($scope = $this->scopeGeneratorResolver->generate())
            || !($zones = $scope->getZones())) {
            return;
        }

        $vpZones = $this->zoneRepository->findForPapVotePlaces($votePlaces->toArray());
        foreach ($vpZones as $vpZone) {
            if (!$this->zoneRepository->isInZones([$vpZone], $zones)) {
                $this
                    ->context
                    ->buildViolation($constraint->messageNotInManagedZone)
                    ->atPath('votePlaces')
                    ->addViolation()
                ;
            }
        }
    }
}

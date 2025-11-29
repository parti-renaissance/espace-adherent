<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Pap\Campaign;
use App\Entity\Pap\VotePlace;
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
        ZoneRepository $zoneRepository,
    ) {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
        $this->campaignRepository = $campaignRepository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @param Campaign $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PapCampaignVotePlaces) {
            throw new UnexpectedTypeException($constraint, PapCampaignVotePlaces::class);
        }

        if (null === $value) {
            return;
        }

        $votePlaces = $value->getVotePlaces()->toArray();

        if ($this->campaignRepository->countCampaignsByVotePlaces($votePlaces, $value)) {
            $this
                ->context
                ->buildViolation($constraint->messageAnotherCampaign)
                ->atPath('votePlaces')
                ->addViolation()
            ;

            return;
        }

        if (!($scope = $this->scopeGeneratorResolver->generate())
            || !($zones = $scope->getZones())) {
            return;
        }

        $vpZones = array_filter(array_map(function (VotePlace $votePlace) {
            return $votePlace->zone;
        }, $votePlaces));

        foreach ($vpZones as $vpZone) {
            if (!$this->zoneRepository->isInZones([$vpZone], $zones)) {
                $this
                    ->context
                    ->buildViolation($constraint->messageNotInManagedZone)
                    ->atPath('votePlaces')
                    ->addViolation()
                ;

                return;
            }
        }
    }
}

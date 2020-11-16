<?php

namespace App\Form\DataTransformer;

use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AdminTerritorialCouncilAdherentMembershipDataTransformer implements DataTransformerInterface
{
    public const REFERENT_TERRITORIAL_COUNCIL = 'referentTerritorialCouncil';
    public const REFERENT_JAM_TERRITORIAL_COUNCIL = 'referentJamTerritorialCouncil';
    public const LRE_MANAGER_TERRITORIAL_COUNCIL = 'lreManagerTerritorialCouncil';
    public const GOVERNMENT_MEMBER_TERRITORIAL_COUNCIL = 'governmentMemberTerritorialCouncil';

    private const MAP = [
        self::REFERENT_TERRITORIAL_COUNCIL => TerritorialCouncilQualityEnum::REFERENT,
        self::REFERENT_JAM_TERRITORIAL_COUNCIL => TerritorialCouncilQualityEnum::REFERENT_JAM,
        self::LRE_MANAGER_TERRITORIAL_COUNCIL => TerritorialCouncilQualityEnum::LRE_MANAGER,
        self::GOVERNMENT_MEMBER_TERRITORIAL_COUNCIL => TerritorialCouncilQualityEnum::GOVERNMENT_MEMBER,
    ];

    /** @var TerritorialCouncilMembership */
    private $membership;
    private $skip = false;

    public function transform($value)
    {
        if ($value instanceof TerritorialCouncilMembership) {
            $this->membership = $value;

            $transformedValue = [];

            foreach (self::MAP as $fieldName => $qualityName) {
                $transformedValue[$fieldName] = $value->hasQuality($qualityName) ? $value->getTerritorialCouncil() : null;
            }

            if (0 === \count(array_filter($transformedValue))) {
                $this->skip = true;
            }

            return $transformedValue;
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (\is_array($value)) {
            $filtered = array_filter($value);

            $count = \count($filtered);

            if ($count < 1) {
                return $this->skip ? $this->membership : null;
            }

            if (\count($filtered) > 1) {
                throw new TransformationFailedException('Adherent cannot be a member of many councils');
            }

            $quality = self::MAP[key($filtered)];

            /** @var TerritorialCouncil $council */
            $council = current($filtered);

            if ($this->membership) {
                $membership = $this->membership;
            } else {
                $membership = new TerritorialCouncilMembership($council);
            }

            $membership->setTerritorialCouncil($council);
            $membership->clearQualities();
            $membership->addQuality(new TerritorialCouncilQuality($quality, $council->getNameCodes()));

            return $membership;
        }

        return null;
    }
}

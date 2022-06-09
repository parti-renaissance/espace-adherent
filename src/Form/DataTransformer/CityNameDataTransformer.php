<?php

namespace App\Form\DataTransformer;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\FranceCities\FranceCities;
use Symfony\Component\Form\DataTransformerInterface;

class CityNameDataTransformer implements DataTransformerInterface
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        if (method_exists($value, 'setCityName') && method_exists($value, 'getCity')) {
            if ($value->getCity() && false !== strpos($value->getCity(), '-')) {
                list(, $inseeCode) = explode('-', $value->getCity());
                $city = $this->franceCities->getCityByInseeCode($inseeCode);

                $value->setCityName($city ? $city->getName() : null);
            }
        }

        if ($value instanceof ProcurationProxy || $value instanceof ProcurationRequest) {
            if ($value->getVoteCity() && false !== strpos($value->getVoteCity(), '-')) {
                list(, $voteCityInseeCode) = explode('-', $value->getVoteCity());
                $VoteCity = $this->franceCities->getCityByInseeCode($voteCityInseeCode);

                $value->setVoteCityName($VoteCity ? $VoteCity->getName() : null);
            }
        }

        return $value;
    }
}

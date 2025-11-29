<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\FranceCities\FranceCities;
use Symfony\Component\Form\DataTransformerInterface;

class CityNameDataTransformer implements DataTransformerInterface
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function transform($value): mixed
    {
        return $value;
    }

    public function reverseTransform($value): mixed
    {
        if (method_exists($value, 'setCityName') && method_exists($value, 'getCity')) {
            if ($value->getCity() && str_contains($value->getCity(), '-')) {
                [, $inseeCode] = explode('-', $value->getCity());
                $city = $this->franceCities->getCityByInseeCode($inseeCode);

                $value->setCityName($city ? $city->getName() : null);
            }
        }

        return $value;
    }
}

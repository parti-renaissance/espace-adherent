<?php

namespace App\Form\DataTransformer;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CityToInseeCodeTransformer implements DataTransformerInterface
{
    private $cityRepository;

    public function __construct(CityRepository $cityRepository)
    {
        $this->cityRepository = $cityRepository;
    }

    public function transform($city)
    {
        return $city instanceof City ? $city->getInseeCode() : null;
    }

    public function reverseTransform($inseeCode)
    {
        $city = $this->cityRepository->findOneBy(['inseeCode' => $inseeCode]);

        if (!$city) {
            throw new TransformationFailedException(sprintf('A City with INSEE code "%d" does not exist.', $inseeCode));
        }

        return $city;
    }
}

<?php

declare(strict_types=1);

namespace App\Twig;

use App\FranceCities\CityValueObject;
use App\FranceCities\FranceCities;
use Twig\Extension\RuntimeExtensionInterface;

class AppIntlRuntime implements RuntimeExtensionInterface
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function getCityByInseeCode(string $code): ?CityValueObject
    {
        return $this->franceCities->getCityByInseeCode($code);
    }
}

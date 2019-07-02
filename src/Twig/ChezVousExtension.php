<?php

namespace AppBundle\Twig;

use AppBundle\ChezVous\MeasureChoiceLoader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ChezVousExtension extends AbstractExtension
{
    private $measureFactory;

    public function __construct(MeasureChoiceLoader $measureFactory)
    {
        $this->measureFactory = $measureFactory;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_chez_vous_measure_type_keys_map', [$this, 'getMeasureTypeKeysMap']),
        ];
    }

    public function getMeasureTypeKeysMap(): array
    {
        return $this->measureFactory->getTypeKeysMap();
    }
}

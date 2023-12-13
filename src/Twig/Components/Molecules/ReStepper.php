<?php

/** @see templates/components/molecules/ReStepper.html.twig */

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReStepper
{
    public array $steps = [];
    public int|string $initStep = 0;
    public string $id;

    private function getJsonSteps(): string
    {
        return json_encode($this->steps);
    }

    public function getJsProps(): string
    {
        return "{
            steps: JSON.parse('{$this->getJsonSteps()}'),
            initStep: {$this->initStep},
            id: '{$this->id}'
        }";
    }
}

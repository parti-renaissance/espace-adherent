<?php

declare(strict_types=1);

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReStepper
{
    public array $steps = [];
    public int|string $initStep = 0;
    public string $id;
    public string $color = 'blue';

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

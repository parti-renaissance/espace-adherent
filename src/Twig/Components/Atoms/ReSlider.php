<?php

declare(strict_types=1);

namespace App\Twig\Components\Atoms;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReSlider
{
    public int $min;
    public int $max;
    public int|string $value;
    public string $onChange = '() => {}';
    public string $id = 'null';

    public ?int $stepBy = null;
    public ?int $step = null;
    public ?string $pipe = null;

    private function getStep(): int
    {
        return $this->step ?? ($this->stepBy ?? 0);
    }

    private function getStepBy(): int
    {
        return $this->stepBy ?? ($this->step ?? 0);
    }

    private function getPipe(): string
    {
        return $this->pipe ?? 'null';
    }

    public function getJsProps(): string
    {
        return "{
            min: $this->min,
            max: $this->max,
            step: {$this->getStep()},
            stepBy: {$this->getStepBy()},
            value: $this->value,
            onChange: $this->onChange,
            pipe: {$this->getPipe()},
        }";
    }
}

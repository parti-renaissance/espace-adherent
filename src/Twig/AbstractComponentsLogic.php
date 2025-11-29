<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\UX\TwigComponent\Attribute\PreMount;
use TailwindMerge\TailwindMerge;

abstract class AbstractComponentsLogic
{
    public ?string $class = null;
    private TailwindMerge $tw;

    protected array $props = [];

    public function __construct()
    {
        $this->tw = TailwindMerge::instance();
    }

    #[PreMount]
    public function preMount(array $data): array
    {
        $this->props = $this->parseProps($data);

        return $data;
    }

    protected function parseProps(array $props): array
    {
        $parsedProps = [];
        foreach ($props as $key => $value) {
            if (\is_string($value) && str_starts_with($value, 'x:')) {
                $parsedProps[$key] = [
                    'alpine' => substr($value, 2),
                    'twig' => null,
                ];
            } else {
                $parsedProps[$key] = [
                    'alpine' => null,
                    'twig' => $value,
                ];
            }
        }

        return $parsedProps;
    }

    public function getTw(...$classes): string
    {
        return $this->tw->merge([...$classes, $this->class]);
    }

    public function getProps(): array
    {
        return $this->props;
    }
}

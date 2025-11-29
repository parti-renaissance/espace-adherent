<?php

declare(strict_types=1);

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReContributionOpt
{
    public string $status = '';
    public int $price = 0;
    public string $type = '';
    public ?bool $isMember = null;
    public ?string $tagged = null;
    public ?string $onChange = null;

    private function translateType($type): string
    {
        return match ($type) {
            'simple' => 'simple',
            'support' => 'soutien',
            'classic' => 'classique',
            'united' => 'solidaire',
            'custom' => 'personnalisée',
            default => '',
        };
    }

    public function getTitle(): string
    {
        $typeCapitalized = ucfirst($this->translateType($this->type));
        $priceString = 'custom' !== $this->type ? "de {$this->price} €" : '';

        return ($this->isMember ? 'Cotisation' : 'Adhésion')." {$typeCapitalized} {$priceString}";
    }

    public function getImage(): array
    {
        return [
            'href' => "/images/icons/re-badges/badge-{$this->type}.svg",
            'alt' => "Adhésion {$this->type}",
        ];
    }

    public function getPrintedPosters(): float
    {
        return $this->price * 5;
    }

    public function getPriceAfterTaxDeduction(): string
    {
        $priceAfterTaxDeduction = $this->price * 0.34;

        return "{$priceAfterTaxDeduction} €";
    }

    private function getOnChange(): string
    {
        return $this->onChange ?? 'null';
    }

    public function getJsProps(): string
    {
        return "{
            price: {$this->price},
            onChange: {$this->onChange},
        }";
    }
}

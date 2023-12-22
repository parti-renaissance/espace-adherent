<?php

namespace App\Twig\Components\Molecules;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ReContributionOpt
{
    public string $status = '';
    public int $price = 0;
    public string $type = '';
    public ?string $isMember = null;
    public ?string $tagged = null;
    public ?string $onChange = null;

    private function translateType($type)
    {
        switch ($type) {
            case 'simple':
                return 'simple';
            case 'support':
                return 'soutien';
            case 'classic':
                return 'classique';
            case 'united':
                return 'solidaire';
            case 'custom':
                return 'personnalisée';
            default:
                return '';
        }
    }

    public function getTitle()
    {
        $typeCapitalized = ucfirst($this->translateType($this->type));
        $priceString = 'custom' !== $this->type ? "de {$this->price} €" : '';

        return ($this->isMember ? 'Cotisation' : 'Adhésion')." {$typeCapitalized} {$priceString}";
    }

    public function getImage()
    {
        return [
            'href' => "/images/icons/re-badges/badge-{$this->type}.svg",
            'alt' => "Adhésion {$this->type}",
        ];
    }

    public function getPrintedPosters()
    {
        return $this->price * 5;
    }

    public function getPriceAfterTaxDeduction()
    {
        $priceAfterTaxDeduction = $this->price * 0.34;

        return "{$priceAfterTaxDeduction} €";
    }

    private function getOnChange(): string
    {
        return $this->onChange ?? 'null';
    }

    public function getJsProps()
    {
        return "{
            price: {$this->price},
            onChange: {$this->onChange},
        }";
    }
}

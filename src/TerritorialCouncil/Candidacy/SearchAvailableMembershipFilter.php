<?php

namespace App\TerritorialCouncil\Candidacy;

use Symfony\Component\Validator\Constraints as Assert;

class SearchAvailableMembershipFilter
{
    /**
     * @var string|null
     */
    #[Assert\NotBlank(message: 'Vous devez choisir la qualité de votre candidature')]
    private $quality;

    /**
     * @var string|null
     */
    #[Assert\NotBlank(message: 'Veuillez utiliser la recherche pour retrouver des candidats')]
    private $query;

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(?string $quality): void
    {
        $this->quality = $quality;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): void
    {
        $this->query = $query;
    }
}

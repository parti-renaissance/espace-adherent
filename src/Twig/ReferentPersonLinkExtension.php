<?php

namespace App\Twig;

use App\Entity\Referent;
use App\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ReferentPersonLinkExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('get_ref_person_link', [$this, 'getRefPersonLink']),
        ];
    }

    public function getRefPersonLink(
        PersonOrganizationalChartItem $personOrganizationalChartItem,
        ?Referent $referent
    ): ?ReferentPersonLink {
        if (!$referent) {
            return null;
        }

        foreach ($referent->getReferentPersonLinks() as $referentPersonLink) {
            if ($referentPersonLink->getPersonOrganizationalChartItem() === $personOrganizationalChartItem) {
                return $referentPersonLink;
            }
        }

        return null;
    }
}

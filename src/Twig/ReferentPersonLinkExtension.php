<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Referent;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Twig\Extension\AbstractExtension;

class ReferentPersonLinkExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_ref_person_link', [$this, 'getRefPersonLink']),
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

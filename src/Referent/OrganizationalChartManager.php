<?php

namespace App\Referent;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\Common\Persistence\ObjectManager;

class OrganizationalChartManager
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function delete(ReferentPersonLink $personLink): void
    {
        if ($personLink->getId()) {
            $this->em->remove($personLink);
            $this->em->flush();
        }
    }

    public function save(ReferentPersonLink $personLink): void
    {
        if (!$personLink->getId()) {
            $this->em->persist($personLink);
        }

        $this->em->flush();
    }
}

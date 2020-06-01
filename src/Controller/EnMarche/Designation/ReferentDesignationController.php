<?php

namespace App\Controller\EnMarche\Designation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/comites/{committee_slug}/designations", name="app_referent_designations")
 *
 * @ParamConverter("committee", options={"mapping": {"committee_slug": "slug"}})
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentDesignationController extends AbstractDesignationController
{
    protected function getSpaceType(): string
    {
        return 'referent';
    }
}

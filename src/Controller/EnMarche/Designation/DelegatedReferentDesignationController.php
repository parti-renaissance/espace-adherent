<?php

namespace App\Controller\EnMarche\Designation;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent-delegue/{delegated_access_uuid}/comites/{committee_slug}/designations", name="app_referent_designations_delegated")
 *
 * @ParamConverter("committee", options={"mapping": {"committee_slug": "slug"}})
 *
 * @Security("is_granted('ROLE_DELEGATED_REFERENT')")
 */
class DelegatedReferentDesignationController extends ReferentDesignationController
{
    use AccessDelegatorTrait;
}

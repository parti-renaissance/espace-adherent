<?php

namespace App\Controller\EnMarche\EventManager;

use App\Controller\AccessDelegatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-senateur-partage/{delegated_access_uuid}", name="app_senator_event_manager_delegated_")
 *
 * @Security("is_granted('ROLE_DELEGATED_SENATOR') and is_granted('HAS_DELEGATED_ACCESS_EVENTS', request)")
 */
class DelegatedSenatorEventManagerController extends SenatorEventManagerController
{
    use AccessDelegatorTrait;
}

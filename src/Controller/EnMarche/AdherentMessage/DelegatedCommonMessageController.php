<?php

namespace App\Controller\EnMarche\AdherentMessage;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/{delegated_access_uuid}/adherent-message", name="app_message_common_delegated_")
 *
 * @Security("is_granted('ROLE_MESSAGE_REDACTOR')")
 */
class DelegatedCommonMessageController extends CommonMessageController
{
}

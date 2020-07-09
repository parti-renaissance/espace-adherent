<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/messagerie", name="app_message_referent_")
 *
 * @Security("is_granted('ROLE_REFERENT') or (is_granted('ROLE_DELEGATED_REFERENT') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES'))")
 */
class ReferentMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::REFERENT;
    }
}

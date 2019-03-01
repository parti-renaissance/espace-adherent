<?php

namespace AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/messagerie", name="app_message_referent_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::REFERENT;
    }
}

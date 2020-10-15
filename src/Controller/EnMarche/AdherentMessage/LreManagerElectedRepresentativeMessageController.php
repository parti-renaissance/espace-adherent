<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-la-republique-ensemble/messagerie-elus", name="app_message_lre_manager_elected_representative_")
 *
 * @Security("is_granted('ROLE_LRE')")
 */
class LreManagerElectedRepresentativeMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE;
    }
}

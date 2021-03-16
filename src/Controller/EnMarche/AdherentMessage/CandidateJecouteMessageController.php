<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat/messagerie-jemarche", name="app_message_candidate_jecoute_")
 *
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES'))")
 */
class CandidateJecouteMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::CANDIDATE_JECOUTE;
    }

    protected function isCanary(): bool
    {
        return true;
    }
}

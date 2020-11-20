<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat/messagerie", name="app_message_candidate_")
 *
 * @Security("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES'))")
 */
class CandidateMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::CANDIDATE;
    }
}

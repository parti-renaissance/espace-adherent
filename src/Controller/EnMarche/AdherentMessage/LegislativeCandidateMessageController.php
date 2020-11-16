<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-candidat-legislative/messagerie", name="app_message_legislative_candidate_")
 *
 * @Security("is_granted('ROLE_LEGISLATIVE_CANDIDATE')")
 */
class LegislativeCandidateMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::LEGISLATIVE_CANDIDATE;
    }
}

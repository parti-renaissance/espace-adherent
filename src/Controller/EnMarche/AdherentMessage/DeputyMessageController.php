<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-depute/messagerie', name: 'app_message_deputy_')]
#[Security("is_granted('ROLE_DEPUTY') or (is_granted('ROLE_DELEGATED_DEPUTY') and is_granted('HAS_DELEGATED_ACCESS_MESSAGES'))")]
class DeputyMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::DEPUTY;
    }
}

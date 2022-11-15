<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-municipales-2020/messagerie", name="app_message_municipal_chief_")
 *
 * @IsGranted("ROLE_MUNICIPAL_CHIEF")
 */
class MunicipalChiefMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::MUNICIPAL_CHIEF;
    }
}

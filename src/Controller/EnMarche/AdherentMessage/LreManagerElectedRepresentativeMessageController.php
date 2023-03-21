<?php

namespace App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/espace-la-republique-ensemble/messagerie-elus', name: 'app_message_lre_manager_elected_representative_')]
#[IsGranted('ROLE_LRE')]
class LreManagerElectedRepresentativeMessageController extends AbstractMessageController
{
    protected function getMessageType(): string
    {
        return AdherentMessageTypeEnum::LRE_MANAGER_ELECTED_REPRESENTATIVE;
    }

    protected function getMessageFilterTemplate(AbstractAdherentMessage $message): string
    {
        return sprintf('message/filter/%s.html.twig', $message->getType());
    }
}

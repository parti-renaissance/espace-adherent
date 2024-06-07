<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Security("is_granted('IS_FEATURE_GRANTED', 'messages') and (message.getAuthor() == user or user.hasDelegatedFromUser(message.getAuthor(), 'messages'))")]
class SendTestAdherentMessageController extends AbstractController
{
    private $manager;

    public function __construct(AdherentMessageManager $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(AbstractAdherentMessage $message)
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        if ($this->manager->sendTest($message, $user)) {
            return $this->json('OK');
        }

        return $this->json('Une erreur inconnue est survenue', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

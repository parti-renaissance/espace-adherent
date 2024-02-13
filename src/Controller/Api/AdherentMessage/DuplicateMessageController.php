<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Security("is_granted('ROLE_MESSAGE_REDACTOR') and (data.getAuthor() == user or user.hasDelegatedFromUser(data.getAuthor(), 'messages'))")]
class DuplicateMessageController extends AbstractController
{
    public function __construct(private readonly AdherentMessageManager $manager)
    {
    }

    public function __invoke(AbstractAdherentMessage $data): Response
    {
        $this->manager->duplicate($data);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}

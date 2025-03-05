<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and (subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'messages') or user.hasDelegatedFromUser(subject.getAuthor(), 'messages_vox'))"), subject: 'data')]
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

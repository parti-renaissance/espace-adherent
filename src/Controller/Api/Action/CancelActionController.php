<?php

namespace App\Controller\Api\Action;

use App\Entity\Action\Action;
use App\JeMengage\Push\Command\NotifyForActionCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'actions') and (subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'actions'))"), subject: 'action')]
class CancelActionController extends AbstractController
{
    public function __invoke(Action $action, EntityManagerInterface $manager, MessageBusInterface $bus): Response
    {
        if ($action->isCancelled()) {
            throw new BadRequestHttpException('This action is already cancelled');
        }

        $action->cancel();
        $manager->flush();

        $bus->dispatch(new NotifyForActionCommand($action->getUuid(), NotifyForActionCommand::EVENT_CANCEL));

        return $this->json('OK');
    }
}

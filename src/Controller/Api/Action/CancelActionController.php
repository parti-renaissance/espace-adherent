<?php

namespace App\Controller\Api\Action;

use App\Entity\Action\Action;
use App\JeMarche\Command\ActionCancelledNotificationCommand;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

#[Security("is_granted('IS_FEATURE_GRANTED', 'actions') and (action.getAuthor() == user or user.hasDelegatedFromUser(action.getAuthor(), 'actions'))")]
class CancelActionController extends AbstractController
{
    public function __invoke(Action $action, EntityManagerInterface $manager, MessageBusInterface $bus): Response
    {
        if ($action->isCancelled()) {
            throw new BadRequestHttpException('This action is already cancelled');
        }

        $action->cancel();
        $manager->flush();

        $bus->dispatch(new ActionCancelledNotificationCommand($action->getUuid()));

        return $this->json('OK');
    }
}

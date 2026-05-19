<?php

declare(strict_types=1);

namespace App\Controller\Api\Action;

use App\Action\ActionEvent;
use App\Entity\Action\Action;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[IsGranted(new Expression("subject.getAuthor() == user or user.hasDelegatedFromUser(subject.getAuthor(), 'actions')"), subject: 'action')]
class CancelActionController extends AbstractController
{
    public function __invoke(Action $action, EntityManagerInterface $manager, EventDispatcherInterface $dispatcher): Response
    {
        if ($action->isCancelled()) {
            throw new BadRequestHttpException('This action is already cancelled');
        }

        $action->cancel();
        $manager->flush();

        $dispatcher->dispatch(new ActionEvent($action->getAuthor(), $action), Events::ACTION_CANCELLED);

        return $this->json('OK');
    }
}

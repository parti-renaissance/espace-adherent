<?php

namespace App\Controller\Api\Action;

use App\Entity\Action\Action;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Security("is_granted('IS_FEATURE_GRANTED', 'actions') and (action.getAuthor() == user or user.hasDelegatedFromUser(action.getAuthor(), 'actions'))")]
class CancelActionController extends AbstractController
{
    public function __invoke(Action $action, EntityManagerInterface $manager): Response
    {
        if (Action::STATUS_CANCELLED === $action->status) {
            throw new BadRequestHttpException('This action is already cancelled');
        }

        $action->cancel();
        $manager->flush();

        return $this->json('OK');
    }
}

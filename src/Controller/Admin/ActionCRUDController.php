<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Action\ActionEvent;
use App\Entity\Action\Action;
use App\Events;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionCRUDController extends CRUDController
{
    public function cancelAction(Request $request, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        /** @var Action $action */
        $action = $this->admin->getSubject();

        $this->admin->checkAccess('edit', $action);

        if ($action->isCancelled()) {
            $this->addFlash('sonata_flash_error', 'Cette action est déjà annulée.');

            return $this->redirect($this->admin->generateObjectUrl('show', $action));
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $this->validateCsrfToken($request, 'admin.action.cancel');

            $action->cancel();
            $entityManager->flush();

            $dispatcher->dispatch(new ActionEvent($action->getAuthor(), $action), Events::ACTION_CANCELLED);

            $this->addFlash('sonata_flash_success', 'L\'action a bien été annulée.');

            return $this->redirect($this->admin->generateObjectUrl('show', $action));
        }

        return $this->renderWithExtraParams('admin/CRUD/confirm.html.twig', [
            'csrf_token' => $this->getCsrfToken('admin.action.cancel'),
            'action' => 'cancel',
            'message' => 'Êtes-vous sûr de vouloir annuler cette action ?',
            'object' => $action,
            'cancel_action' => 'show',
        ]);
    }
}

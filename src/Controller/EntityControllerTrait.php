<?php

namespace App\Controller;

use App\Form\DeleteEntityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

trait EntityControllerTrait
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    public function insert($entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function update(): void
    {
        $this->getEntityManager()->flush();
    }

    public function remove($entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function createDeleteForm(string $action, string $tokenId, Request $request = null): Form
    {
        $form = $this->get('form.factory')->create(DeleteEntityType::class, null, [
            'action' => $action,
            'csrf_token_id' => $tokenId,
        ]);

        return $request ? $form->handleRequest($request) : $form;
    }
}

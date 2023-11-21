<?php

namespace App\Controller;

use App\Form\DeleteEntityType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;

trait EntityControllerTrait
{
    /** @var EntityManagerInterface */
    private $entityManager;

    #[Required]
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

    public function createDeleteForm(string $action, string $tokenId, Request $request = null): FormInterface
    {
        $form = $this->createForm(DeleteEntityType::class, null, [
            'action' => $action,
            'csrf_token_id' => $tokenId,
        ]);

        return $request ? $form->handleRequest($request) : $form;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Address\PostAddressFactory;
use App\Committee\CommitteeManager;
use App\Committee\DTO\CommitteeCommand;
use App\Entity\Committee;
use App\Exception\BaseGroupException;
use App\Form\Admin\ApproveCommitteeCommandType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCommitteeCRUDController extends CRUDController
{
    public function approveAction(
        Request $request,
        Committee $committee,
        CommitteeManager $committeeManager,
        PostAddressFactory $addressFactory,
    ): Response {
        $this->admin->checkAccess('approve');

        /** @var CommitteeCommand $command */
        $command = CommitteeCommand::createFromCommittee($committee);
        $form = $this->createForm(ApproveCommitteeCommandType::class, $command, [
            'validation_groups' => ['Default', 'with_provisional_supervisors'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                try {
                    $committee->setName($command->getName());
                    $committee->setDescription($command->getDescription());
                    $committee->setNameLocked($command->isNameLocked());
                    $address = $addressFactory->createFromAddress($command->getAddress(), true);
                    if (!$committee->getPostAddress()->equals($address)) {
                        $committee->setPostAddress($address);
                    }
                    $committee->setNameLocked($command->isNameLocked());
                    $committeeManager->approveCommittee($committee);

                    $this->addFlash('sonata_flash_success', \sprintf('Le comité « %s » a été approuvé avec succès.', $committee->getName()));
                } catch (BaseGroupException $exception) {
                    throw $this->createNotFoundException(\sprintf('Committee %u must not be approved in order to be approved.', $committee->getId()), $exception);
                }

                return $this->redirectToList();
            }

            return $this->renderWithExtraParams('admin/committee/approve/confirm.html.twig', [
                'form' => $form->createView(),
                'action' => 'approve',
                'object' => $command,
                'elements' => $this->admin->getShow(),
            ]);
        }

        return $this->renderWithExtraParams('admin/committee/approve/edit.html.twig', [
            'form' => $form->createView(),
            'action' => 'approve',
            'object' => $command,
            'elements' => $this->admin->getShow(),
        ]);
    }
}

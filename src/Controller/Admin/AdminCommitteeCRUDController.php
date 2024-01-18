<?php

namespace App\Controller\Admin;

use App\Address\PostAddressFactory;
use App\Committee\CommitteeManagementAuthority;
use App\Committee\DTO\CommitteeCommand;
use App\Committee\Exception\MultipleReferentsFoundException;
use App\Entity\Adherent;
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
        CommitteeManagementAuthority $committeeManagementAuthority,
        PostAddressFactory $addressFactory
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
                    if ($adherentPSF = $command->getProvisionalSupervisorFemale()) {
                        $committee->updateProvisionalSupervisor($adherentPSF);
                    }

                    if ($adherentPSM = $command->getProvisionalSupervisorMale()) {
                        $committee->updateProvisionalSupervisor($adherentPSM);
                    }
                    $committeeManagementAuthority->approve($committee);
                    $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été approuvé avec succès.', $committee->getName()));
                } catch (BaseGroupException $exception) {
                    throw $this->createNotFoundException(sprintf('Committee %u must not be approved in order to be approved.', $committee->getId()), $exception);
                }

                try {
                    $committeeManagementAuthority->notifyReferentsForApproval($committee);
                } catch (MultipleReferentsFoundException $exception) {
                    $this->addFlash('warning', sprintf(
                        'Attention, plusieurs référents (%s) ont été trouvés dans le département de ce nouveau comité.
                                Aucun email de notification pour la validation de ce comité ne leur a été envoyé.
                                Nommez un seul référent pour permettre les notifications de ce type.',
                        implode(', ', array_map(function (Adherent $referent) {
                            return $referent->getEmailAddress();
                        }, $exception->getReferents()->toArray()))
                    ));
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

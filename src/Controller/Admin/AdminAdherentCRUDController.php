<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Adherent\BanManager;
use AppBundle\Adherent\CertificationManager;
use AppBundle\Entity\Adherent;
use AppBundle\Form\ConfirmActionType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAdherentCRUDController extends CRUDController
{
    public function banAction(Request $request, BanManager $adherentManagementAuthority): Response
    {
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('ban', $adherent);

        if (!$adherentManagementAuthority->canBan($adherent)) {
            $this->addFlash(
                'error',
                'Il est possible d\'exclure uniquement les adhérents sans aucun rôle (animateur, référent etc.).'
            );

            return $this->redirectToRoute('admin_app_adherent_edit', [
                'id' => $adherent->getId(),
            ]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $adherentManagementAuthority->ban($adherent, $this->getUser());

                $this->addFlash('success', sprintf('L\'adhérent <b>%s</b> a bien été exclu.', $adherent->getFullName()));
            }

            return $this->redirectToList();
        }

        return $this->renderWithExtraParams('admin/adherent/ban.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'ban',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function certifyAction(Request $request, CertificationManager $certificationManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('ban', $adherent);

        if ($adherent->isCertified()) {
            $this->addFlash('error', sprintf(
                'L\'adhérent <b>%s</b> est déjà certifié.',
                $adherent->getFullName()
            ));

            return $this->redirectToRoute('admin_app_adherent_edit', [
                'id' => $adherent->getId(),
            ]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->certify($adherent);

                $this->addFlash('success', sprintf(
                    'L\'adhérent <b>%s</b> a bien été certifié.',
                    $adherent->getFullName()
                ));
            }

            return $this->redirectToList();
        }

        return $this->render('admin/adherent/certify.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'certify',
            'elements' => $this->admin->getShow(),
        ]);
    }
}

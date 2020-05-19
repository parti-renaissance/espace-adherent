<?php

namespace App\Controller\Admin;

use App\Adherent\AdherentExtractCommand;
use App\Adherent\AdherentExtractCommandHandler;
use App\Adherent\BanManager;
use App\Adherent\CertificationAuthorityManager;
use App\Adherent\CertificationPermissions;
use App\Entity\Adherent;
use App\Form\Admin\Extract\AdherentExtractType;
use App\Form\ConfirmActionType;
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

            return $this->redirectTo($adherent);
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

    public function certifyAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('certify', $adherent);

        if (!$this->isGranted(CertificationPermissions::CERTIFY, $adherent)) {
            $this->addFlash('error', sprintf(
                'L\'adhérent <b>%s</b> est déjà certifié.',
                $adherent->getFullName()
            ));

            return $this->redirectTo($adherent);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->certify($adherent, $this->getUser());

                $this->addFlash('success', sprintf(
                    'L\'adhérent <b>%s</b> a bien été certifié.',
                    $adherent->getFullName()
                ));
            }

            return $this->redirectTo($adherent);
        }

        return $this->renderWithExtraParams('admin/adherent/certify.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'certify',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function uncertifyAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('uncertify', $adherent);

        if (!$this->isGranted(CertificationPermissions::UNCERTIFY, $adherent)) {
            $this->addFlash('error', sprintf(
                'L\'adhérent <b>%s</b> n\'est pas certifié.',
                $adherent->getFullName()
            ));

            return $this->redirectTo($adherent);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->uncertify($adherent, $this->getUser());

                $this->addFlash('warning', sprintf(
                    'L\'adhérent <b>%s</b> n\'est plus certifié.',
                    $adherent->getFullName()
                ));
            }

            return $this->redirectTo($adherent);
        }

        return $this->renderWithExtraParams('admin/adherent/uncertify.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'uncertify',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function extractAction(
        Request $request,
        AdherentExtractCommandHandler $adherentExtractCommandHandler
    ): Response {
        $this->admin->checkAccess('extract');

        $adherentExtractCommand = new AdherentExtractCommand();

        $form = $this
            ->createForm(AdherentExtractType::class, $adherentExtractCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $adherentExtractCommandHandler->createResponse($adherentExtractCommand);
        }

        return $this->render('admin/adherent/extract/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

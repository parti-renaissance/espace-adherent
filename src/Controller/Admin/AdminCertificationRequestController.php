<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Adherent\CertificationAuthorityManager;
use AppBundle\Entity\CertificationRequest;
use AppBundle\Form\ConfirmActionType;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCertificationRequestController extends CRUDController
{
    private $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function approveAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var CertificationRequest $certificationRequest */
        $certificationRequest = $this->admin->getSubject();

        $this->admin->checkAccess('approve', $certificationRequest);

        if ($certificationRequest->isApproved()) {
            $this->addFlash('error', sprintf(
                'La demande de certification <b>%d</b> est déjà approuvée.',
                $certificationRequest->getId()
            ));

            return $this->redirectToRoute('admin_app_certification_request_show', [
                'id' => $certificationRequest->getId(),
            ]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->approve($certificationRequest, $this->getUser());

                $this->addFlash('success', sprintf(
                    'L\'adhérent <b>%s</b> a bien été certifié.',
                    $certificationRequest->getAdherent()->getFullName()
                ));
            }

            return $this->redirectTo($certificationRequest);
        }

        return $this->render('admin/certification_request/approve.html.twig', [
            'form' => $form->createView(),
            'object' => $certificationRequest,
            'action' => 'approve',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function refuseAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var CertificationRequest $certificationRequest */
        $certificationRequest = $this->admin->getSubject();

        $this->admin->checkAccess('refuse', $certificationRequest);

        if ($certificationRequest->isRefused()) {
            $this->addFlash('error', sprintf(
                'La demande de certification <b>%d</b> est déjà refusée.',
                $certificationRequest->getId()
            ));

            return $this->redirectToRoute('admin_app_certification_request_show', [
                'id' => $certificationRequest->getId(),
            ]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->refuse($certificationRequest, $this->getUser());

                $this->addFlash('success', sprintf(
                    'La demande de certification de l\'adhérent <b>%s</b> a bien été refusée.',
                    $certificationRequest->getAdherent()->getFullName()
                ));
            }

            return $this->redirectTo($certificationRequest);
        }

        return $this->render('admin/certification_request/refuse.html.twig', [
            'form' => $form->createView(),
            'object' => $certificationRequest,
            'action' => 'refuse',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function documentAction(): Response
    {
        /** @var CertificationRequest $certificationRequest */
        $certificationRequest = $this->admin->getSubject();

        $filePath = $certificationRequest->getPathWithDirectory();

        if (!$this->storage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this CertificationRequest.');
        }

        $response = new Response($this->storage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $certificationRequest->getDocumentMimeType(),
        ]);

        return $response;
    }
}

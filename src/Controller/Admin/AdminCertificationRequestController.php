<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Adherent\CertificationAuthorityManager;
use AppBundle\Adherent\CertificationPermissions;
use AppBundle\Entity\CertificationRequest;
use AppBundle\Form\ConfirmActionType;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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

        if (!$this->isGranted(CertificationPermissions::APPROVE, $certificationRequest)) {
            $this->addFlash('error', sprintf(
                'La demande de certification <b>%d</b> est déjà traitée et ne peut être approuvée.',
                $certificationRequest->getId()
            ));

            return $this->redirectTo($certificationRequest);
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

        return $this->renderWithExtraParams('admin/certification_request/approve.html.twig', [
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

        if (!$this->isGranted(CertificationPermissions::REFUSE, $certificationRequest)) {
            $this->addFlash('error', sprintf(
                'La demande de certification <b>%d</b> est déjà traitée et ne peut être refusée.',
                $certificationRequest->getId()
            ));

            return $this->redirectTo($certificationRequest);
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

        return $this->renderWithExtraParams('admin/certification_request/refuse.html.twig', [
            'form' => $form->createView(),
            'object' => $certificationRequest,
            'action' => 'refuse',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function blockAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var CertificationRequest $certificationRequest */
        $certificationRequest = $this->admin->getSubject();

        $this->admin->checkAccess('block', $certificationRequest);

        if (!$this->isGranted(CertificationPermissions::BLOCK, $certificationRequest)) {
            $this->addFlash('error', sprintf(
                'La demande de certification <b>%d</b> est déjà traitée et ne peut être bloquée.',
                $certificationRequest->getId()
            ));

            return $this->redirectTo($certificationRequest);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->block($certificationRequest, $this->getUser());

                $this->addFlash('success', sprintf(
                    'La demande de certification de l\'adhérent <b>%s</b> a bien été bloquée.',
                    $certificationRequest->getAdherent()->getFullName()
                ));
            }

            return $this->redirectTo($certificationRequest);
        }

        return $this->renderWithExtraParams('admin/certification_request/block.html.twig', [
            'form' => $form->createView(),
            'object' => $certificationRequest,
            'action' => 'block',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function documentAction(Request $request): Response
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

        if ($request->query->has('download')) {
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $certificationRequest->getDocumentName()
            );

            $response->headers->set('Content-Disposition', $disposition);
        }

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationPermissions;
use App\Adherent\Certification\CertificationRequestBlockCommand;
use App\Adherent\Certification\CertificationRequestRefuseCommand;
use App\Entity\CertificationRequest;
use App\Form\Admin\CertificationRequestBlockCommandType;
use App\Form\Admin\CertificationRequestRefuseCommandType;
use App\Form\ConfirmActionType;
use App\Utils\HttpUtils;
use League\Flysystem\FilesystemOperator;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCertificationRequestController extends CRUDController
{
    private $storage;

    public function __construct(FilesystemOperator $defaultStorage)
    {
        $this->storage = $defaultStorage;
    }

    public function approveAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var CertificationRequest $certificationRequest */
        $certificationRequest = $this->admin->getSubject();

        $this->admin->checkAccess('approve', $certificationRequest);

        if (!$this->isGranted(CertificationPermissions::APPROVE, $certificationRequest)) {
            $this->addFlash('error', \sprintf(
                'La demande de certification <b>%d</b> est déjà traitée et ne peut être approuvée.',
                $certificationRequest->getId()
            ));

            return $this->redirectTo($request, $certificationRequest);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->approve($certificationRequest, $this->getUser());

                $this->addFlash('success', \sprintf(
                    'L\'adhérent <b>%s</b> a bien été certifié.',
                    $certificationRequest->getAdherent()->getFullName()
                ));
            }

            return $this->redirectTo($request, $certificationRequest);
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
            $this->addFlash('error', \sprintf(
                'La demande de certification <b>%d</b> est déjà traitée et ne peut être refusée.',
                $certificationRequest->getId()
            ));

            return $this->redirectTo($request, $certificationRequest);
        }

        $refuseCommand = new CertificationRequestRefuseCommand($certificationRequest, $this->getUser());

        $form = $this
            ->createForm(CertificationRequestRefuseCommandType::class, $refuseCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $certificationManager->refuse($refuseCommand);

            $this->addFlash('success', \sprintf(
                'La demande de certification de l\'adhérent <b>%s</b> a bien été refusée.',
                $certificationRequest->getAdherent()->getFullName()
            ));

            return $this->redirectTo($request, $certificationRequest);
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
            $this->addFlash('error', \sprintf(
                'La demande de certification <b>%d</b> est déjà traitée et ne peut être bloquée.',
                $certificationRequest->getId()
            ));

            return $this->redirectTo($request, $certificationRequest);
        }

        $blockCommand = new CertificationRequestBlockCommand($certificationRequest, $this->getUser());

        $form = $this
            ->createForm(CertificationRequestBlockCommandType::class, $blockCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $certificationManager->block($blockCommand);

            $this->addFlash('success', \sprintf(
                'La demande de certification de l\'adhérent <b>%s</b> a bien été bloquée.',
                $certificationRequest->getAdherent()->getFullName()
            ));

            return $this->redirectTo($request, $certificationRequest);
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

        return HttpUtils::createResponse(
            $this->storage,
            $certificationRequest->getPathWithDirectory(),
            $certificationRequest->getDocumentName(),
            $certificationRequest->getDocumentMimeType(),
            $request->query->has('download')
        );
    }
}

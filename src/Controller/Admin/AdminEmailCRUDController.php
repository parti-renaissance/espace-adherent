<?php

namespace App\Controller\Admin;

use App\Entity\Administrator;
use App\Entity\Email\EmailLog;
use App\Entity\Email\TransactionalEmailTemplate;
use App\Form\Admin\UnlayerContentType;
use App\Mailer\Command\AsyncSendMessageCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\EmailTemplateMessage;
use App\Mailer\Template\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AdminEmailCRUDController extends CRUDController
{
    public function resendAction(MessageBusInterface $bus): Response
    {
        /** @var EmailLog $email */
        $email = $this->admin->getSubject();

        $this->admin->checkAccess('resend', $email);

        $bus->dispatch(new AsyncSendMessageCommand($email->getUuid(), true));

        $this->addFlash('sonata_flash_success', 'Email a été renvoyé');

        return $this->redirectToList();
    }

    public function sendTestAction(TransactionalEmailTemplate $template, MailerService $transactionalMailer): Response
    {
        $this->admin->checkAccess('send_test', $template);

        /** @var Administrator $admin */
        $admin = $this->getUser();

        $transactionalMailer->sendMessage(EmailTemplateMessage::create($template, $admin->getEmailAddress()), false);

        $this->addFlash('sonata_flash_success', 'Email de test a été renvoyé à <strong>'.$admin->getEmailAddress().'</strong>');

        return $this->redirectToList();
    }

    public function contentAction(TransactionalEmailTemplate $template, Request $request): Response
    {
        $this->admin->checkAccess('content', $template);

        $form = $this->createFormBuilder($template)
            ->add('jsonContent', HiddenType::class)
            ->add('content', UnlayerContentType::class, [
                'label' => false,
            ])
            ->getForm()
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->admin->update($template);

            $this->addFlash('sonata_flash_success', 'Le contenu a été mis à jour');

            if ('save_and_preview' === $request->request->get('action')) {
                return $this->redirect($this->admin->generateObjectUrl('preview', $template));
            }

            return $this->redirect($this->admin->generateObjectUrl('content', $template));
        }

        return $this->renderWithExtraParams('admin/email/content.html.twig', [
            'object' => $template,
            'form' => $form->createView(),
            'action' => 'content',
        ]);
    }

    public function previewAction(TransactionalEmailTemplate $template): Response
    {
        $this->admin->checkAccess('preview', $template);

        return $this->renderWithExtraParams('admin/email/preview.html.twig', [
            'object' => $template,
            'action' => 'preview',
        ]);
    }

    public function sendToProdAction(TransactionalEmailTemplate $template, HttpClientInterface $templateWebhookClient, SerializerInterface $serializer): Response
    {
        $this->admin->checkAccess('content', $template);

        $response = $templateWebhookClient->request('POST', '/templates', [
            'json' => [
                'identifier' => $template->identifier,
                'subject' => $template->subject,
                'content' => $template->getContent(),
                'jsonContent' => $template->getJsonContent(),
                'parent' => [
                    'identifier' => $template->parent?->identifier,
                ],
            ],
        ]);

        if (200 === $response->getStatusCode()) {
            $this->addFlash('sonata_flash_success', 'Le template a été envoyé en production');
        } else {
            $this->addFlash('sonata_flash_error', 'Erreur lors de l\'envoi du template en production');
        }

        return $this->redirect($this->admin->generateObjectUrl('list', $template));
    }

    public function previewContentAction(TransactionalEmailTemplate $template, Manager $templateManager): Response
    {
        $this->admin->checkAccess('preview_content', $template);

        return new Response($templateManager->getTemplateContent($template));
    }

    public function duplicateAction(TransactionalEmailTemplate $template, EntityManagerInterface $entityManager): Response
    {
        $newTemplate = clone $template;

        $entityManager->persist($newTemplate);
        $entityManager->flush();

        $this->addFlash('sonata_flash_success', 'Le template a été dupliqué');

        return $this->redirect($this->admin->generateUrl('edit', ['id' => $newTemplate->getId()]));
    }
}

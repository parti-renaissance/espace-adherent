<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Mailer\EmailTemplateService;
use AppBundle\Mailer\Message\MessageRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/email/template")
 * @Security("has_role('ROLE_APP_ADMIN_EMAIL_TEMPLATE')")
 */
class AdminEmailTemplateController extends Controller
{
    /**
     * @Route(name="app_admin_email_template_list")
     * @Method("GET")
     */
    public function listAction(MessageRegistry $messageRegistry): Response
    {
        return $this->render('admin/mailer/template/list.html.twig', [
            'templates' => $messageRegistry->getAllMessages(),
        ]);
    }

    /**
     * @Route("/{name}", name="app_admin_email_template_show")
     * @Method("GET")
     */
    public function showAction(string $name, MessageRegistry $messageRegistry, EmailTemplateService $emailTemplateService): Response
    {
        $template = [
            'name' => $name,
            'class' => $messageRegistry->getMessageClass($name),
            'subject' => $emailTemplateService->renderSubject($name),
        ];

        return $this->render('admin/mailer/template/show.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/html/{name}", name="app_admin_email_template_html")
     * @Method("GET")
     */
    public function htmlAction(string $name, EmailTemplateService $emailTemplateService): Response
    {
        $this->get('profiler')->disable();

        return new Response($emailTemplateService->renderBody($name));
    }
}

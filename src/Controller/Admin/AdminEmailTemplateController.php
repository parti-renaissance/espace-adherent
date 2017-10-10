<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/email/template")
 */
class AdminEmailTemplateController extends Controller
{
    /**
     * @Route(name="app_admin_email_template_list")
     * @Method("GET")
     * @Security("has_role('ROLE_APP_ADMIN_EMAIL_TEMPLATE_LIST')")
     */
    public function listAction(): Response
    {
        return $this->render('admin/mailer/template/list.html.twig', [
            'templates' => $this->get('app.mailer.message_registry')->getTypes(),
        ]);
    }

    /**
     * @Route("/{name}", name="app_admin_email_template_show")
     * @Method("GET")
     * @Security("has_role('ROLE_APP_ADMIN_EMAIL_TEMPLATE_VIEW')")
     */
    public function showAction(string $name): Response
    {
        $template = [
            'name' => $name,
            'class' => $this->get('app.mailer.message_registry')->getTypeByTemplate($name),
            'subject' => $this->renderBlock($name, 'subject'),
        ];

        return $this->render('admin/mailer/template/show.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/html/{name}", name="app_admin_email_template_html")
     * @Method("GET")
     * @Security("has_role('ROLE_APP_ADMIN_EMAIL_TEMPLATE_VIEW')")
     */
    public function htmlAction(string $name): Response
    {
        $this->get('profiler')->disable();

        return new Response($this->renderBlock($name, 'body_html'));
    }

    /**
     * @Route("/text/{name}", name="app_admin_email_template_text")
     * @Method("GET")
     * @Security("has_role('ROLE_APP_ADMIN_EMAIL_TEMPLATE_VIEW')")
     */
    public function textAction(string $name): Response
    {
        $this->get('profiler')->disable();

        return new Response($this->renderBlock($name, 'body_text'));
    }

    private function renderBlock(string $templateName, string $blockName): string
    {
        $template = $this->get('twig')->load(sprintf('email/template/%s_message.html.twig', $templateName));

        if (!$template->hasBlock($blockName)) {
            throw $this->createNotFoundException(sprintf('The template "%s" has no "%s" block.', $templateName, $blockName));
        }

        return $template->renderBlock($blockName);
    }
}

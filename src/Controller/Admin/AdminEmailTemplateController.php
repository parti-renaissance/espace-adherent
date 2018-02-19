<?php

namespace AppBundle\Controller\Admin;

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
    public function listAction(): Response
    {
        return $this->render('admin/mailer/template/list.html.twig', [
            'templates' => $this->get(MessageRegistry::class)->getAllMessages(),
        ]);
    }

    /**
     * @Route("/{name}", name="app_admin_email_template_show")
     * @Method("GET")
     */
    public function showAction(string $name): Response
    {
        $template = [
            'name' => $name,
            'class' => $this->get(MessageRegistry::class)->getMessageClass($name),
            'subject' => $this->renderBlock($name, 'subject'),
        ];

        return $this->render('admin/mailer/template/show.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/html/{name}", name="app_admin_email_template_html")
     * @Method("GET")
     */
    public function htmlAction(string $name): Response
    {
        $this->get('profiler')->disable();

        return new Response($this->renderBlock($name, 'body_html'));
    }

    /**
     * @Route("/text/{name}", name="app_admin_email_template_text")
     * @Method("GET")
     */
    public function textAction(string $name): Response
    {
        $this->get('profiler')->disable();

        return new Response($this->renderBlock($name, 'body_text'));
    }

    private function renderBlock(string $templateName, string $blockName): string
    {
        /* @var \Twig_TemplateWrapper $template */
        $template = $this->get('twig')->load("email/$templateName.html.twig");

        if (!$template->hasBlock($blockName)) {
            throw $this->createNotFoundException("The template \"$templateName\" has no \"$blockName\" block.");
        }

        return $template->renderBlock($blockName);
    }
}

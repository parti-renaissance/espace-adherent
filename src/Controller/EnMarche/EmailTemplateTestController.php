<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\MailjetTemplate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/email-template")
 */
class EmailTemplateTestController extends Controller
{
    /**
     * @Route(name="app_email_template_list")
     * @Method("GET")
     */
    public function listAction(): Response
    {
        $this->checkEnvironment();

        $templates = $this->getDoctrine()->getRepository(MailjetTemplate::class)->findAll();

        usort($templates, function (MailjetTemplate $a, MailjetTemplate $b) {
            return strcmp($a->getMessageClass(), $b->getMessageClass());
        });

        return $this->render('email/list.html.twig', [
            'templates' => $templates,
        ]);
    }

    /**
     * @Route("/html/{name}", name="app_email_template_html")
     * @Method("GET")
     */
    public function htmlAction(string $name): Response
    {
        $this->checkEnvironment();

        return $this->renderBlock($name, 'body_html');
    }

    /**
     * @Route("/text/{name}", name="app_email_template_text")
     * @Method("GET")
     */
    public function textAction(string $name): Response
    {
        $this->checkEnvironment();

        return $this->renderBlock($name, 'body_text');
    }

    private function renderBlock(string $templateName, string $blockName): Response
    {
        // profiler toolbar should not interact with the template content
        $this->get('profiler')->disable();

        $template = $this->get('twig')->load(sprintf('email/template/%s.html.twig', $templateName));

        if (!$template->hasBlock($blockName)) {
            throw $this->createNotFoundException(sprintf('The template "%s" has no "%s" block.', $templateName, $blockName));
        }

        return new Response($template->renderBlock($blockName));
    }

    private function checkEnvironment()
    {
        if (!in_array($this->get('kernel')->getEnvironment(), ['dev', 'test'])) {
            throw $this->createNotFoundException();
        }
    }
}

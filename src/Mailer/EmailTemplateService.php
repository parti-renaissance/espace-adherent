<?php

namespace AppBundle\Mailer;

use AppBundle\Mailer\Exception\MailerException;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRegistry;
use Twig\Environment as TwigEnvironment;

class EmailTemplateService
{
    private $twig;
    private $messageRegistry;

    public function __construct(TwigEnvironment $twig, MessageRegistry $messageRegistry)
    {
        $this->twig = $twig;
        $this->messageRegistry = $messageRegistry;
    }

    public function assertMessageVariablesAgainstTemplateVariable(Message $message, EmailTemplate $emailTemplate): void
    {
        $foundVars = $emailTemplate->getVariableNames();
        $expectedVars = $this->findVariables($message);
        $errors = [];

        $unexpectedVarsFound = array_diff($foundVars, $expectedVars);
        $expectedVarsNotFound = array_diff($expectedVars, $foundVars);

        foreach ($unexpectedVarsFound as $name) {
            $errors[] = "Variable \"$name\" was not expected in the template.";
        }

        foreach ($expectedVarsNotFound as $name) {
            $errors[] = "Variable \"$name\" was not found in the template.";
        }

        if ($errors) {
            throw new MailerException(implode(PHP_EOL, $errors));
        }
    }

    private function findVariables(Message $message): array
    {
        $templateName = $this->messageRegistry->getMessageTemplate($message);
        $html = $this->twig->render("email/$templateName.html.twig");

        preg_match_all('/@@(?<name>[a-z0-9_]+)@@/', $html, $matches);

        return array_unique($matches['name']);
    }

    public function renderSubject($name): string
    {
        return $this->renderBlock($name, 'subject');
    }

    public function renderBody($name): string
    {
        return $this->renderBlock($name, 'body_html');
    }

    private function renderBlock(string $name, string $blockName): string
    {
        $messageClass = $this->messageRegistry->getMessageClass($name);
        $templateName = $this->messageRegistry->getTemplateName($messageClass);
        /* @var \Twig_TemplateWrapper $template */
        $template = $this->twig->load("email/$templateName.html.twig");

        if (!$template->hasBlock($blockName)) {
            throw new \LogicException("Block '$blockName' is missing in message template");
        }

        return $template->renderBlock($blockName);
    }
}

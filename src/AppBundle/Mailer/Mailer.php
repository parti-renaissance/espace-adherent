<?php

namespace AppBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param EngineInterface       $templating
     * @param array                 $parameters
     */
    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function sendInvitationMail(string $email, string $fullName, string $message)
    {
        $template = $this->parameters['invitation.template'];
        $rendered = $this->templating->render($template, [
            'fullName' => $fullName,
            'message' => $message,
        ]);
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['invitation'], $email);
    }

    /**
     * {@inheritdoc}
     */
    private function sendEmailMessage(string $renderedTemplate, string $fromEmail, string $toEmail)
    {
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = array_shift($renderedLines);
        $body = implode("\n", $renderedLines);
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body, 'text/html');
        $this->mailer->send($message);
    }
}

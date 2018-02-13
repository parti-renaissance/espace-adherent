<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\AbstractEmailClient;
use AppBundle\Mailer\EmailTemplateClientInterface;
use AppBundle\Mailer\Template;
use AppBundle\Mailer\Exception\MailjetException;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class EmailTemplateClient extends AbstractEmailClient implements EmailTemplateClientInterface
{
    const MAILJET_TEMPLATE_EDITMODE = 2;
    const MAILJET_TEMPLATE_OWNERTYPE = 'user';
    const MAILJET_TEMPLATE_PURPOSES = ['transactional'];

    /**
     * @var Twig_Environment
     */
    private $templating;
    private $remoteTemplates = [];

    public function setTemplating(Twig_Environment $templating)
    {
        $this->templating = $templating;
    }

    public function synchronize(string $template): void
    {
        if (!$this->has($template)) {
            $this->create($template);
        }

        $this->update($template);
    }

    private function list(): array
    {
        $response = $this->request('GET', 'REST/template', [
            'query' => [
                'Author' => 'En Marche!',
                'Copyright' => 'En Marche ©',
                'EditMode' => static::MAILJET_TEMPLATE_EDITMODE,
                'OwnerType' => static::MAILJET_TEMPLATE_OWNERTYPE,
                'Purposes' => static::MAILJET_TEMPLATE_PURPOSES,
                'Limit' => 0,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException('Unable to retrieve the template list.');
        }

        return array_reduce($this->getBody($response)['Data'], function (array $templates, array $data): array {
            $templates[$data['Name']] = $data;

            return $templates;
        }, $this->remoteTemplates);
    }

    private function has(string $template): bool
    {
        if (empty($this->remoteTemplates)) {
            $this->remoteTemplates = $this->list();
        }

        return array_key_exists($template, $this->remoteTemplates);
    }

    private function create(Template $template): array
    {
        $requestPayload = [
            'Author' => 'En Marche!',
            'Copyright' => 'En Marche ©',
            'Name' => $template->getName(),
            'EditMode' => static::MAILJET_TEMPLATE_EDITMODE,
            'OwnerType' => static::MAILJET_TEMPLATE_OWNERTYPE,
            'Purposes' => static::MAILJET_TEMPLATE_PURPOSES,
        ];

        $response = $this->request('POST', 'REST/template', [
            'body' => json_encode($requestPayload),
        ]);

        if (Response::HTTP_CREATED !== $response->getStatusCode()) {
            throw new MailerException('Unable to create remote email template.');
        }

        return $this->getBody($response);
    }

    private function update(Template $template): array
    {
        $templateWrapper = $this->templating->load(sprintf(
            'email/template/%s_message.html.twig',
            $template->getName())
        );

        $requestPayload = [
            'Headers' => [
                'From' => $template->getFrom(),
                'Subject' => $templateWrapper->renderBlock('subject'),
            ],
            'Html-part' => $templateWrapper->renderBlock('body_html'),
        ];

        if ($templateWrapper->hasBlock('body_text')) {
            $requestPayload['Text-part'] = $templateWrapper->renderBlock('body_text');
        }

        $uri = sprintf(
            'REST/template/%s/detailcontent',
            sprintf('%s|%s', static::MAILJET_TEMPLATE_OWNERTYPE, $template->getName())
        );

        $response = $this->httpClient->request('POST', $uri, [
            'auth' => [$this->publicKey, $this->privateKey],
            'json' => $requestPayload,
        ]);

        if (Response::HTTP_CREATED !== $response->getStatusCode()) {
            throw new MailerException('Unable to create remote email template.');
        }

        return $this->getBody($response);
    }
}

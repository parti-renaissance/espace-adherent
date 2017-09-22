<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\Model\EmailTemplate;
use AppBundle\Mailer\EmailTemplateClientInterface;
use AppBundle\Mailjet\Exception\MailjetException;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class EmailTemplateClient extends MailjetClient implements EmailTemplateClientInterface
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

    /**
     * @param \AppBundle\Entity\MailjetTemplate $template
     */
    public function synchronize(EmailTemplate $template): void
    {
        if (!$this->has($template)) {
            $this->create($template);
        }

        $this->update($template);
    }

    private function list(): array
    {
        $response = $this->httpClient->request('GET', 'REST/template', [
            'auth' => [$this->publicKey, $this->privateKey],
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
            throw new MailjetException('Unable to retrieve the template list.');
        }

        return array_reduce($this->getBody($response)['Data'], function (array $templates, array $data): array {
            $templates[$data['Name']] = $data;

            return $templates;
        }, $this->remoteTemplates);
    }

    private function has(Emailtemplate $template): bool
    {
        if (empty($this->remoteTemplates)) {
            $this->remoteTemplates = $this->list();
        }

        return array_key_exists($template->getName(), $this->remoteTemplates);
    }

    /**
     * @param \AppBundle\Entity\MailjetTemplate $template
     */
    private function create(EmailTemplate $template): array
    {
        $requestPayload = [
            'Author' => 'En Marche!',
            'Copyright' => 'En Marche ©',
            'Name' => $template->getName(),
            'EditMode' => static::MAILJET_TEMPLATE_EDITMODE,
            'OwnerType' => static::MAILJET_TEMPLATE_OWNERTYPE,
            'Purposes' => static::MAILJET_TEMPLATE_PURPOSES,
        ];

        $response = $this->httpClient->request('POST', 'REST/template', [
            'auth' => [$this->publicKey, $this->privateKey],
            'body' => json_encode($requestPayload),
        ]);

        if (Response::HTTP_CREATED !== $response->getStatusCode()) {
            throw new MailjetException('Unable to create remote email template.');
        }

        return $this->getBody($response);
    }

    /**
     * @param \AppBundle\Entity\MailjetTemplate $template
     */
    private function update(EmailTemplate $template): array
    {
        $templateWrapper = $this->templating->load(sprintf('email/template/%s.html.twig', $template->getName()));

        $requestPayload = [
            'Headers' => [
                'From' => $template->getSender(),
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
            throw new MailjetException('Unable to create remote email template.');
        }

        return $this->getBody($response);
    }
}

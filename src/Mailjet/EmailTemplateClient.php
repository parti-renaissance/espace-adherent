<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\AbstractEmailClient;
use AppBundle\Mailer\EmailTemplateClientInterface;
use GuzzleHttp\ClientInterface as Guzzle;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;

/**
 * @see https://dev.mailjet.com/email-api/v3
 * @see https://dev.mailjet.com/guides/
 */
class EmailTemplateClient extends AbstractEmailClient implements EmailTemplateClientInterface
{
    private const MAILJET_TEMPLATE_EDITMODE = 2; // 2 means html mode
    private const MAILJET_TEMPLATE_OWNERTYPE = 'apikey';

    private $templating;
    private $remoteTemplates;
    private $senderEmail;
    private $senderName;
    private $purpose;

    public function __construct(
        Guzzle $httpClient,
        Environment $templating,
        string $publicKey,
        string $privateKey,
        string $senderEmail,
        string $senderName,
        string $purpose
    ) {
        $this->templating = $templating;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->purpose = $purpose;

        parent::__construct($httpClient, $publicKey, $privateKey);
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
                'OwnerType' => static::MAILJET_TEMPLATE_OWNERTYPE,
                'Purposes' => $this->purpose,
                'Limit' => 0,
            ],
        ]);

        $templates = [];

        foreach ($this->getBody($response)['Data'] as $templateData) {
            $templates[$templateData['Name']] = $templateData;
        }

        return $templates;
    }

    private function has(string $template): bool
    {
        if (null === $this->remoteTemplates) {
            $this->remoteTemplates = $this->list();
        }

        return isset($this->remoteTemplates[$template]);
    }

    private function create(string $template): void
    {
        $requestPayload = [
            'Author' => 'En Marche!',
            'Copyright' => 'En Marche ©',
            'Name' => $template,
            'EditMode' => static::MAILJET_TEMPLATE_EDITMODE,
            'OwnerType' => static::MAILJET_TEMPLATE_OWNERTYPE,
            'Purposes' => [$this->purpose],
        ];

        $this->request('POST', 'REST/template', ['body' => \GuzzleHttp\json_encode($requestPayload)]);
    }

    private function update(string $template): void
    {
        $templateWrapper = $this->templating->load(sprintf('email/%s.html.twig', $template));

        $subject = $templateWrapper->renderBlock('subject');
        $bodyHtml = $templateWrapper->renderBlock('body_html');

        $requestPayload = [
            'Headers' => [
                'Subject' => $this->toMailjetVariables($subject),
                'From' => sprintf('"%s" <%s>', $this->senderName, $this->senderEmail),
            ],
            'Html-part' => $this->toMailjetVariables($bodyHtml),
        ];

        $uri = sprintf('REST/template/%s|%s/detailcontent', static::MAILJET_TEMPLATE_OWNERTYPE, $template);

        $this->request('POST', $uri, ['json' => $requestPayload]);
    }

    /**
     * Transform placeholders @@var_name@@ to mailjet format, e.g.: {{var:var_name}}
     */
    private function toMailjetVariables(string $text): string
    {
        return preg_replace('/@@(\w+)@@/m', '{{var:$1}}', $text);
    }

    protected function getBody(ResponseInterface $response): array
    {
        return \GuzzleHttp\json_decode($response->getBody(), true);
    }
}

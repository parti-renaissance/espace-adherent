<?php

namespace App\Mailchimp\SignUp;

use App\Entity\Adherent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SignUpHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $subscriptionGroupId;
    private $subscriptionIds;
    private $client;
    private $mailchimpOrgId;
    private $listId;

    public function __construct(
        HttpClientInterface $client,
        int $subscriptionGroupId,
        array $subscriptionIds,
        string $mailchimpOrgId,
        string $listId
    ) {
        $this->subscriptionGroupId = $subscriptionGroupId;
        $this->subscriptionIds = $subscriptionIds;
        $this->mailchimpOrgId = $mailchimpOrgId;
        $this->listId = $listId;
        $this->client = $client;
    }

    public function signUpAdherent(Adherent $adherent): bool
    {
        try {
            $response = $this->client->request('POST', '/subscribe/post', [
                'query' => [
                    'u' => $this->mailchimpOrgId,
                    'id' => $this->listId,
                ],
                'body' => $this->getFormData($adherent),
                'headers' => [
                    'origin' => 'https://en-marche.fr',
                    'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15',
                ],
            ]);

            return 200 === $response->getStatusCode();
        } catch (ClientExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return false;
    }

    private function getFormData(Adherent $adherent)
    {
        $formData = [
            'EMAIL' => $adherent->getEmailAddress(),
            $this->getTokenKey() => null,
        ];

        foreach ($this->subscriptionIds as $code => $id) {
            if ($adherent->hasSubscriptionType($code)) {
                $formData[sprintf('group[%d][%d]', $this->subscriptionGroupId, $id)] = $id;
            }
        }

        return $formData;
    }

    private function getTokenKey(): string
    {
        return sprintf('b_%s_%s', $this->mailchimpOrgId, $this->listId);
    }
}

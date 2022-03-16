<?php

namespace App\Recaptcha;

use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;

class RecaptchaApiClient implements RecaptchaApiClientInterface
{
    private RecaptchaEnterpriseServiceClient $client;
    private string $projectId;
    private string $siteKey;

    public function __construct(string $projectId, string $siteKey)
    {
        $this->projectId = $projectId;
        $this->siteKey = $siteKey;

        $this->client = new RecaptchaEnterpriseServiceClient();

    }

    public function verify(string $answer, string $clientIp = null): bool
    {
        $assessment = $this->createAssessment($answer);

        $response = $this->client->createAssessment('projects/'.$this->projectId, $assessment);

        return $response->getTokenProperties()->getValid();
    }

    private function createEvent(string $token): Event
    {
        return (new Event())
            ->setSiteKey($this->siteKey)
            ->setToken($token)
        ;
    }

    private function createAssessment(string $token): Assessment
    {
        return (new Assessment())
            ->setEvent($this->createEvent($token))
        ;
    }
}

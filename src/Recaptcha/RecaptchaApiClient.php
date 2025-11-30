<?php

declare(strict_types=1);

namespace App\Recaptcha;

use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;

class RecaptchaApiClient implements RecaptchaApiClientInterface
{
    public const NAME = 'google_enterprise';

    private RecaptchaEnterpriseServiceClient $client;
    private string $projectId;
    private string $defaultSiteKey;

    public function __construct(string $projectId, string $defaultSiteKey)
    {
        $this->projectId = $projectId;
        $this->defaultSiteKey = $defaultSiteKey;

        $this->client = new RecaptchaEnterpriseServiceClient();
    }

    public function supports(string $name): bool
    {
        return self::NAME === $name;
    }

    public function verify(string $token, ?string $siteKey): bool
    {
        $formattedParent = $this->client::projectName($this->projectId);
        $assessment = $this->createAssessment($token, $siteKey);

        $response = $this->client->createAssessment($formattedParent, $assessment);

        return $response->getTokenProperties()->getValid();
    }

    private function createEvent(string $token, ?string $siteKey): Event
    {
        return new Event()
            ->setSiteKey($siteKey ?? $this->defaultSiteKey)
            ->setToken($token)
        ;
    }

    private function createAssessment(string $token, ?string $siteKey): Assessment
    {
        return new Assessment()
            ->setEvent($this->createEvent($token, $siteKey))
        ;
    }
}

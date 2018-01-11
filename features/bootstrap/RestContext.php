<?php

use Behatch\Context\RestContext as BehatchRestContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;

class RestContext extends BehatchRestContext
{
    private $httpCallResultPool;

    public function __construct(Request $request, HttpCallResultPool $httpCallResultPool)
    {
        parent::__construct($request);

        $this->httpCallResultPool = $httpCallResultPool;
    }

    /**
     * @Given I send a :method request to :url with the access token
     */
    public function iSendARequestToWithAccessToken(string $method, string $url): void
    {
        $accessToken = $this->getAccessTokenFromLastResponse();

        $this->iSendARequestTo($method, $url.'?'.http_build_query(['access_token' => $accessToken]));
    }

    /**
     * @Given I add the access token to the Authorization header
     */
    public function iAddTheAccessTokenToTheAuthorizationHeader(): void
    {
        $this->iAddHeaderEqualTo('Authorization', 'Bearer '.$this->getAccessTokenFromLastResponse());
    }

    /**
     * @throws Exception when no access_token is provided
     */
    public function getAccessTokenFromLastResponse(): string
    {
        $json = json_decode($this->httpCallResultPool->getResult()->getValue(), true);

        if (!isset($json['access_token'])) {
            throw new \Exception('No access token provided in the last HTTP response');
        }

        return $json['access_token'];
    }
}

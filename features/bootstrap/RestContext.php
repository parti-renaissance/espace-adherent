<?php

use AppBundle\OAuth\Model\Client;
use AppBundle\OAuth\Model\Scope;
use AppBundle\Repository\OAuth\AccessTokenRepository;
use Behatch\Context\RestContext as BehatchRestContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;
use League\OAuth2\Server\CryptKey;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RestContext extends BehatchRestContext
{
    private $httpCallResultPool;
    private $accessTokentRepository;
    private $privateCryptKey;

    public function __construct(
        Request $request,
        HttpCallResultPool $httpCallResultPool,
        AccessTokenRepository $accessTokenRepository,
        ContainerInterface $container
    ) {
        parent::__construct($request);

        $this->httpCallResultPool = $httpCallResultPool;
        $this->accessTokentRepository = $accessTokenRepository;
        $this->privateCryptKey = new CryptKey($container->getParameter('env(SSL_PRIVATE_KEY)'));
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
     * @Given I add the access token :identifier to the Authorization headers
     */
    public function iAddTheSpecificAccessTokenToTheAuthorizationHeader(string $identifier): void
    {
        $this->iAddHeaderEqualTo('Authorization', 'Bearer '.$this->getJwtAccessTokenByIdentifier($identifier));
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

    private function getJwtAccessTokenByIdentifier($identifier): string
    {
        /** @var \AppBundle\Entity\OAuth\AccessToken $accessToken */
        $accessToken = $this
            ->accessTokentRepository
            ->findAccessTokenByIdentifier($identifier)
        ;

        $client = new Client($accessToken->getClient()->getUuid()->toString(), []);

        $token = new \AppBundle\OAuth\Model\AccessToken();
        $token->setClient($client);
        $token->setIdentifier($identifier);
        $token->setExpiryDateTime(\DateTime::createFromFormat('U', $accessToken->getExpiryDateTime()->getTimestamp()));
        $token->setUserIdentifier($accessToken->getUserIdentifier());

        foreach ($accessToken->getScopes() as $scope) {
            $token->addScope(new Scope($scope));
        }

        return $token->convertToJWT($this->privateCryptKey);
    }
}

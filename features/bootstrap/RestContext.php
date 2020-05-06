<?php

use App\Entity\Adherent;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\OAuth\Model\AccessToken as AccessTokenModel;
use App\OAuth\Model\Client as ClientModel;
use App\OAuth\Model\Scope;
use App\Repository\AdherentRepository;
use App\Repository\OAuth\ClientRepository;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behatch\Context\RestContext as BehatchRestContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\CryptKey;
use Ramsey\Uuid\Uuid;

class RestContext extends BehatchRestContext
{
    private $httpCallResultPool;
    private $entityManager;
    private $privateCryptKey;

    /**
     * @var string|null
     */
    private $accessToken;

    public function __construct(
        Request $request,
        HttpCallResultPool $httpCallResultPool,
        EntityManagerInterface $entityManager,
        string $sslPrivateKey
    ) {
        parent::__construct($request);

        $this->httpCallResultPool = $httpCallResultPool;
        $this->entityManager = $entityManager;
        $this->privateCryptKey = new CryptKey($sslPrivateKey);
    }

    /**
     * @Given I am logged with :email via OAuth client :clientName with scope :scope
     */
    public function iAmLoggedViaOAuthWithClientAndScope(string $email, string $clientName, string $scope): void
    {
        $identifier = uniqid();

        /** @var AdherentRepository $adherentRepository */
        $adherentRepository = $this->entityManager->getRepository(Adherent::class);
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->entityManager->getRepository(Client::class);

        $accessToken = new AccessToken(
            Uuid::uuid5(Uuid::NAMESPACE_OID, $identifier),
            $adherentRepository->findOneByEmail($email),
            $identifier,
            new \DateTime('+10 minutes'),
            $clientRepository->findOneBy(['name' => $clientName])
        );

        $accessToken->addScope($scope);

        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();

        $this->accessToken = $this->getJwtFromAccessToken($accessToken);
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

    public function iSendARequestToWithParameters($method, $url, TableNode $data)
    {
        $this->addAccessTokenToTheAuthorizationHeader();

        return parent::iSendARequestToWithParameters($method, $url, $data);
    }

    public function iSendARequestTo($method, $url, PyStringNode $body = null, $files = [])
    {
        $this->addAccessTokenToTheAuthorizationHeader();

        parent::iSendARequestTo($method, $url, $body, $files);
    }

    public function iSendARequestToWithBody($method, $url, PyStringNode $body)
    {
        $this->addAccessTokenToTheAuthorizationHeader();

        return parent::iSendARequestToWithBody($method, $url, $body);
    }

    private function addAccessTokenToTheAuthorizationHeader(): void
    {
        if (!$this->accessToken) {
            return;
        }

        $this->iAddHeaderEqualTo('Authorization', 'Bearer '.$this->accessToken);
    }

    private function getJwtFromAccessToken(AccessToken $accessToken): string
    {
        $client = new ClientModel($accessToken->getClient()->getUuid()->toString(), []);

        $token = new AccessTokenModel();
        $token->setClient($client);
        $token->setIdentifier($accessToken->getIdentifier());
        $token->setExpiryDateTime(\DateTime::createFromFormat('U', $accessToken->getExpiryDateTime()->getTimestamp()));
        $token->setUserIdentifier($accessToken->getUserIdentifier());

        foreach ($accessToken->getScopes() as $scope) {
            $token->addScope(new Scope($scope));
        }

        return $token->convertToJWT($this->privateCryptKey);
    }
}

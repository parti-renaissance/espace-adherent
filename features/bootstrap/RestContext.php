<?php

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\OAuth\Model\AccessToken as AccessTokenModel;
use App\OAuth\Model\Client as ClientModel;
use App\OAuth\Model\Scope;
use App\Repository\AdherentRepository;
use App\Repository\OAuth\ClientRepository;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behatch\Context\RestContext as BehatchRestContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\CryptKey;
use PHPUnit\Framework\Assert;
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
    private $authorizationCode;

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
     * @Given I am logged with device :device_id via OAuth client :clientName with scope :scope
     */
    public function iAmLoggedWithDeviceViaOAuthWithClientAndScope(
        string $deviceUuid,
        string $clientName,
        string $scope
    ): void {
        $identifier = uniqid();

        /** @var AdherentRepository $adherentRepository */
        $deviceRepository = $this->entityManager->getRepository(Device::class);
        /** @var ClientRepository $clientRepository */
        $clientRepository = $this->entityManager->getRepository(Client::class);

        if (!$device = $deviceRepository->findOneByDeviceUuid($deviceUuid)) {
            $device = new Device(Uuid::uuid4(), $deviceUuid);
            $this->entityManager->persist($device);
        }

        $accessToken = new AccessToken(
            Uuid::uuid5(Uuid::NAMESPACE_OID, $identifier),
            null,
            $identifier,
            new \DateTimeImmutable('+10 minutes'),
            $clientRepository->findOneBy(['name' => $clientName]),
            $device
        );

        $accessToken->addScope($scope);

        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();

        $this->accessToken = $this->getJwtFromAccessToken($accessToken);
    }

    /**
     * @Given I am logged with :email via OAuth client :clientName
     * @Given I am logged with :email via OAuth client :clientName with scope :scopes
     * @Given I am logged with :email via OAuth client :clientName with scopes :scopes
     */
    public function iAmLoggedViaOAuthWithClientAndScope(string $email, string $clientName, ?string $scopes = null): void
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
            new \DateTimeImmutable('+10 minutes'),
            $clientRepository->findOneBy(['name' => $clientName])
        );

        if ($scope) {
            foreach (explode(' ', $scopes) as $scope) {
                $accessToken->addScope($scope);
            }
        }

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

    /**
     * Follow redirect instructions.
     *
     * @param string $page
     *
     * @return void
     *
     * @Then /^I (?:am|should be) redirected(?: to "([^"]*)")?$/
     */
    public function iAmRedirected($page = null)
    {
        $headers = $this->getSession()->getResponseHeaders();

        if (empty($headers['Location']) && empty($headers['location'])) {
            throw new \RuntimeException('The response should contain a "Location" header');
        }

        if (null !== $page) {
            $header = empty($headers['Location']) ? $headers['location'] : $headers['Location'];
            if (\is_array($header)) {
                $header = current($header);
            }

            Assert::assertEquals($this->locatePath($header), $this->locatePath($page), 'The "Location" header points to the correct URI');
        }

        $client = $this->getClient();

        $baseRedirectStrategy = $client->isFollowingRedirects();
        $client->followRedirects(true);
        $client->followRedirect();
        $client->followRedirects($baseRedirectStrategy);
    }

    /**
     * @Given /^I stop following redirections$/
     */
    public function iDontFollowRedirections()
    {
        $client = $this->getClient();

        $client->followRedirects(false);
    }

    private function addAccessTokenToTheAuthorizationHeader(): void
    {
        if (!$this->accessToken) {
            return;
        }

        $this->iAddHeaderEqualTo('Authorization', 'Bearer '.$this->accessToken);
    }

    /**
     * Checks, whether the header name matches to given pattern
     *
     * @Then the header :name should match :value
     */
    public function theHeaderShouldMatch($name, $pattern)
    {
        $actual = $this->request->getHttpHeader($name);

        $this->assert(
            preg_match($pattern, $actual) > 0,
            "The header '$actual' does not match the pattern '$pattern'."
        );
    }

    private function getJwtFromAccessToken(AccessToken $accessToken): string
    {
        $client = new ClientModel($accessToken->getClient()->getUuid()->toString(), []);

        $token = new AccessTokenModel();
        $token->setClient($client);
        $token->setIdentifier($accessToken->getIdentifier());
        $token->setExpiryDateTime($accessToken->getExpiryDateTime());
        $token->setUserIdentifier($accessToken->getUserIdentifier());
        $token->setDeviceIdentifier($accessToken->getDeviceIdentifier());
        $token->setPrivateKey($this->privateCryptKey);

        foreach ($accessToken->getScopes() as $scope) {
            $token->addScope(new Scope($scope));
        }

        return (string) $token;
    }

    /**
     * Returns current active mink session.
     *
     * @return \Symfony\Component\BrowserKit\Client
     *
     * @throws \Behat\Mink\Exception\UnsupportedDriverActionException
     */
    protected function getClient()
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof BrowserKitDriver) {
            $message = 'This step is only supported by the browserkit drivers';

            throw new UnsupportedDriverActionException($message, $driver);
        }

        return $driver->getClient();
    }
}

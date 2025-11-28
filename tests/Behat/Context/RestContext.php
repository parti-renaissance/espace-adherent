<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use App\Entity\Adherent;
use App\Entity\Device;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\OAuth\Model\AccessToken as AccessTokenModel;
use App\OAuth\Model\Client as ClientModel;
use App\OAuth\Model\Scope;
use App\Repository\AdherentRepository;
use App\Repository\OAuth\ClientRepository;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behatch\Context\RestContext as BehatchRestContext;
use Behatch\HttpCall\HttpCallResult;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\HttpCall\Request;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\CryptKey;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

class RestContext extends BehatchRestContext
{
    private HttpCallResultPool $httpCallResultPool;

    private EntityManagerInterface $entityManager;
    private CryptKey $privateCryptKey;
    private ?string $accessToken = null;
    private ?HttpCallResult $savedResponse = null;

    public function __construct(HttpCallResultPool $httpCallResultPool, Request $request)
    {
        parent::__construct($request);

        $this->httpCallResultPool = $httpCallResultPool;
    }

    /**
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope)
    {
        /** @var DIContext $diContext */
        $diContext = $scope->getEnvironment()->getContext(DIContext::class);
        $this->entityManager = $diContext->get('doctrine')->getManager();
        $this->privateCryptKey = new CryptKey($diContext->getParameter('ssl_private_key'), null, false);
        $this->accessToken = null;
    }

    /**
     * @Given I am logged with device :device_id via OAuth client :clientName with scope :scope
     */
    public function iAmLoggedWithDeviceViaOAuthWithClientAndScope(
        string $deviceUuid,
        string $clientName,
        string $scope,
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
            new \DateTimeImmutable('+30 minutes'),
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
            new \DateTimeImmutable('+30 minutes'),
            $clientRepository->findOneBy(['name' => $clientName])
        );

        if ($scopes) {
            foreach (explode(' ', $scopes) as $scope) {
                $accessToken->addScope($scope);
            }
        }

        $this->entityManager->persist($accessToken);
        $this->entityManager->flush();

        $this->accessToken = $this->getJwtFromAccessToken($accessToken);
    }

    /**
     * @When I log out
     */
    public function iLogOut(): void
    {
        $this->accessToken = null;
    }

    /**
     * @Given I send a :method request to :url with the access token
     */
    public function iSendARequestToWithAccessToken(string $method, string $url)
    {
        $accessToken = $this->getAccessTokenFromLastResponse();

        return $this->iSendARequestTo($method, $url.'?'.http_build_query(['access_token' => $accessToken]));
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

    /**
     * @Given I save this response
     */
    public function iSaveThisResponse(): void
    {
        $this->savedResponse = $this->httpCallResultPool->getResult();
    }

    public function iSendARequestTo($method, $url, ?PyStringNode $body = null, $files = [])
    {
        $match = [];
        if (preg_match('/:(last_response|saved_response)\.(\w+):/', $url, $match)) {
            $result = 'saved_response' === $match[1] ? $this->savedResponse : $this->httpCallResultPool->getResult();

            if ($result) {
                $responseData = json_decode($result->getValue(), true);

                if (!empty($responseData[$match[2]])) {
                    $url = str_replace($match[0], $responseData[$match[2]], $url);
                }
            }
        }

        $this->addAccessTokenToTheAuthorizationHeader();

        $this->setAcceptApplicationJsonHeader($url, $method, $body);

        return parent::iSendARequestTo($method, $url, $body, $files);
    }

    public function iSendARequestToWithParameters($method, $url, TableNode $data)
    {
        $this->addAccessTokenToTheAuthorizationHeader();
        $this->setAcceptApplicationJsonHeader($url);

        return parent::iSendARequestToWithParameters($method, $url, $data);
    }

    public function iSendARequestToWithBody($method, $url, PyStringNode $body)
    {
        $this->setAcceptApplicationJsonHeader($url);

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

    private function getJwtFromAccessToken(AccessToken $accessToken): string
    {
        $client = new ClientModel($accessToken->getClient()->getUuid()->toString(), []);

        $token = new AccessTokenModel();
        $token->setClient($client);
        $token->setIdentifier($accessToken->getIdentifier());
        $token->setExpiryDateTime($accessToken->getExpiryDateTime());
        $token->setUserIdentifier($accessToken->getUserIdentifier());
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
     * @throws UnsupportedDriverActionException
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

    private function setAcceptApplicationJsonHeader(
        string $url,
        ?string $method = null,
        ?PyStringNode &$body = null,
    ): void {
        if (preg_match('#^/?(api|oauth)/#', $url) && !str_contains($url, 'oauth/v2/auth')) {
            $this->iAddHeaderEqualTo('Accept', 'application/json');

            if (\in_array($method, ['PUT', 'POST', 'PATCH'], true)) {
                $this->iAddHeaderEqualTo('Content-type', 'application/json');
                if (null === $body) {
                    $body = new PyStringNode(['{}'], 1);
                }
            }
        }
    }
}

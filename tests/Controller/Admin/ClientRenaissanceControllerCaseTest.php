<?php

namespace Tests\App\Controller\Admin;

use App\DataFixtures\ORM\LoadClientData;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\RefreshToken;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('admin')]
class ClientRenaissanceControllerCaseTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;

    public function testCreateLinkFromDashboard(): void
    {
        $crawler = $this->client->request('GET', '/dashboard');

        $this->isSuccessful($this->client->getResponse());

        $addLinks = $crawler->filterXPath('//a[@href="/app/oauth-client/create"]');
        $this->assertGreaterThanOrEqual(1, $addLinks->count(), 'A link to create a Client from the dashboard should exist.');

        $this->client->click($addLinks->first()->link());
        $this->isSuccessful($this->client->getResponse());
    }

    public function testCreateValidClient(): void
    {
        $crawler = $this->client->request('GET', '/app/oauth-client/create');
        $this->isSuccessful($this->client->getResponse());

        $csrfInput = $crawler->filter('form input[id$=__token]')->first();
        $formName = str_replace('__token', '', $csrfInput->attr('id'));

        $form = $crawler->selectButton('Créer')->form();

        $values = $form->getPhpValues()[$formName];
        $values['name'] = 'Name Client 1';
        $values['description'] = 'Description Client 1';
        $values['redirectUris'] = ['http://test.com'];
        $values['allowedGrantTypes'] = ['authorization_code' => 1, 'refresh_token' => 1];
        $values['askUserForAuthorization'] = 1;

        $this->client->request($form->getMethod(), $form->getUri(), [$formName => $values], $form->getPhpFiles());

        $this->assertTrue($this->client->getResponse()->isRedirection());
        $this->assertMatchesRegularExpression('#/app/oauth-client/[\d]+/edit#', $this->client->getResponse()->getTargetUrl(), 'The user should be redirected on the \'edit\' page.');

        $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
    }

    public function testRevokedTokensAfterDelete(): void
    {
        $oauthClient = $this->findClient(LoadClientData::CLIENT_01_UUID);

        $crawler = $this->client->request('GET', \sprintf('/app/oauth-client/%s/delete', $oauthClient->getId()));
        $this->client->submit($crawler->selectButton('Oui, supprimer')->form());

        $accessTokens = $this->findAccessTokensByClient($oauthClient);

        foreach ($accessTokens as $accessToken) {
            $this->assertTrue($accessToken->isRevoked(), 'All the AccessTokens of this Client should be revoked.');
        }

        $refreshTokens = $this->findRefreshTokensByClient($oauthClient);

        foreach ($refreshTokens as $refreshToken) {
            $this->assertTrue($refreshToken->isRevoked(), 'All the RefreshTokens of this AccessToken should be revoked.');
        }

        $this->client->followRedirect();
        $this->isSuccessful($this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateAsAdmin($this->client);
    }

    private function findClient(string $uuid): Client
    {
        return $this
            ->getEntityManager()
            ->getRepository(Client::class)
            ->findClientByUuid(Uuid::fromString($uuid))
        ;
    }

    private function findAccessTokensByClient(Client $client): ?array
    {
        return $this
            ->getEntityManager()
            ->getRepository(AccessToken::class)
            ->findAllAccessTokensByClient($client)
        ;
    }

    private function findRefreshTokensByClient(Client $client): ?array
    {
        return $this
            ->getEntityManager()
            ->getRepository(RefreshToken::class)
            ->createQueryBuilder('rt')
            ->join('rt.accessToken', 'at')
            ->where('at.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult()
        ;
    }
}

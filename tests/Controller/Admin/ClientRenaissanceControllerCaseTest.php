<?php

declare(strict_types=1);

namespace Tests\App\Controller\Admin;

use App\DataFixtures\ORM\LoadClientData;
use App\Entity\OAuth\AccessToken;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\RefreshToken;
use PHPUnit\Framework\Attributes\Group;
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

    public function testTokensRemovedAfterClientDelete(): void
    {
        $oauthClient = $this->findClient(LoadClientData::CLIENT_01_UUID);
        $clientId = $oauthClient->getId();

        $crawler = $this->client->request('GET', \sprintf('/app/oauth-client/%s/delete', $clientId));
        $this->client->submit($crawler->selectButton('Oui, supprimer')->form());

        $this->getEntityManager()->clear();

        // The Client should be physically deleted along with its access/refresh tokens.
        self::assertNull($this->getEntityManager()->getRepository(Client::class)->find($clientId));
        self::assertSame(0, $this->countAccessTokensForClientId($clientId));
        self::assertSame(0, $this->countRefreshTokensForClientId($clientId));

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
            ->findOneByUuid($uuid)
        ;
    }

    private function countAccessTokensForClientId(int $clientId): int
    {
        return (int) $this
            ->getEntityManager()
            ->getRepository(AccessToken::class)
            ->createQueryBuilder('at')
            ->select('COUNT(at.id)')
            ->where('IDENTITY(at.client) = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countRefreshTokensForClientId(int $clientId): int
    {
        return (int) $this
            ->getEntityManager()
            ->getRepository(RefreshToken::class)
            ->createQueryBuilder('rt')
            ->select('COUNT(rt.id)')
            ->join('rt.accessToken', 'at')
            ->where('IDENTITY(at.client) = :clientId')
            ->setParameter('clientId', $clientId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}

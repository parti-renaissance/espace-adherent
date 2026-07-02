# MCP Server — Plateforme Espace Adhérent

> **For agentic workers:** REQUIRED SUB-SKILL: Use `subagent-driven-development` (recommended) or `executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construire un serveur MCP TypeScript (transport stdio) qui expose les endpoints clés de l'API Plateforme comme outils Claude, avec authentification OAuth2 Authorization Code + PKCE via la page de login web existante.

**Architecture:** Serveur MCP local (stdio, lancé par Claude Code comme sous-process), qui gère un flow OAuth2 PKCE au premier lancement (ouvre le navigateur vers `/oauth/v2/auth`, écoute le callback sur `localhost:3742`), persiste le token dans `~/.config/plateforme-mcp/token.json`, et forward chaque tool call comme requête HTTP `Authorization: Bearer <token>` vers l'API Symfony. Deux nouveaux endpoints Symfony sont créés pour les stats agrégées et les tendances de publications. Le serveur Symfony gère toute la logique d'autorisation (voters, rôles, scopes) de façon transparente.

**Tech Stack:** TypeScript 5, `@modelcontextprotocol/sdk` v1.x, Node 20+ (fetch natif), `open` v10 (navigateur), Vitest (tests TS), PHP 8.3 + Symfony 7, PHPUnit 13 (tests PHP)

---

## Périmètre des outils MCP exposés

| Outil | Endpoint Symfony | Statut |
|---|---|---|
| `list_users` | `GET /api/v3/adherents` | existant |
| `get_user` | `GET /api/v3/adherents/{uuid}` | existant |
| `count_users` | `GET /api/v3/adherents/count` | existant |
| `list_publications` | `GET /api/v3/adherent_messages` | existant |
| `get_publication` | `GET /api/v3/adherent_messages/{uuid}` | existant |
| `get_publication_stats` | `GET /api/v3/stats/publication/{uuid}` | existant |
| `get_publications_kpi` | `GET /api/v3/adherent_messages/kpi` | existant |
| `get_publication_trends` | `GET /api/v3/stats/publication/{uuid}/trends` | **à créer** |
| `get_publications_aggregated` | `GET /api/v3/adherent_messages/statistics/aggregated` | **à créer** |
| `list_events` | `GET /api/v3/events` | existant |
| `get_event` | `GET /api/v3/events/{uuid}` | existant |
| `list_actions` | `GET /api/v3/actions` | existant |
| `get_action` | `GET /api/v3/actions/{uuid}` | existant |
| `list_committees` | `GET /api/v3/committees` | existant |
| `get_committee` | `GET /api/v3/committees/{uuid}` | existant |
| `list_zones` | `GET /api/v3/zones` | existant |
| `list_national_events` | `GET /api/v3/national_events` | existant |

---

## Structure des fichiers

### Symfony (nouveaux fichiers)

```
src/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsController.php
src/Controller/Api/GetPublicationTrendsController.php
tests/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsControllerTest.php
tests/Controller/Api/GetPublicationTrendsControllerTest.php
```

`AppHitRepository` : ajout méthode `getDailyStats()`
`AdherentMessageRepository` : ajout méthode `getAggregatedPublicationStats()`

### MCP Server (nouveau package)

```
mcp/
  src/
    index.ts                  # Entry point CLI (stdio)
    server.ts                 # MCP Server + enregistrement tools
    auth/
      pkce.ts                 # Helpers PKCE (verifier, challenge, state)
      oauth.ts                # Flow Authorization Code : ouverture navigateur + callback + échange
      token.ts                # Persistance token (lecture/écriture ~/.config/plateforme-mcp/)
    api/
      client.ts               # HTTP client avec Bearer token + refresh automatique
    tools/
      users.ts                # list_users, get_user, count_users
      publications.ts         # list_publications, get_publication, get_publication_stats, get_publications_kpi, get_publication_trends, get_publications_aggregated
      events.ts               # list_events, get_event
      actions.ts              # list_actions, get_action
      committees.ts           # list_committees, get_committee
      zones.ts                # list_zones
      national-events.ts      # list_national_events
  tests/
    auth/
      pkce.test.ts
      token.test.ts
    tools/
      users.test.ts
      publications.test.ts
  package.json
  tsconfig.json
  .env.example
  README.md
```

---

## Phase 1 — Symfony : client OAuth2 + nouveaux endpoints

### Task 1 : Créer le client OAuth2 MCP en base

**Contexte :** Un client OAuth2 MCP doit exister en DB pour que le flow PKCE fonctionne. Le client doit avoir `AUTHORIZATION_CODE` + `REFRESH_TOKEN`, un redirect URI vers `localhost:3742/callback` (dev) et l'URL déployée (prod/staging), et le scope `jemarche_app`.

**Files :**
- Modify: `src/DataFixtures/ORM/LoadClientData.php` (ajout fixture client MCP pour les tests)
- Create: migration Doctrine pour prod/staging

- [ ] **Step 1 : Vérifier la structure de l'entité Client**

```bash
bin/console doctrine:schema:validate
grep -n "public function __construct" src/Entity/OAuth/Client.php
```

Expected : la signature du constructeur avec `(UuidInterface, string $name, string $description, string $secret, array $grantTypes, array $redirectUris)`.

- [ ] **Step 2 : Ajouter la fixture client MCP dans LoadClientData**

Dans `src/DataFixtures/ORM/LoadClientData.php`, ajouter la constante et la création du client :

```php
public const CLIENT_MCP_UUID = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';

// Dans la méthode load(), après les clients existants :
$clientMcp = new Client(
    Uuid::fromString(self::CLIENT_MCP_UUID),
    'Claude MCP',
    'Serveur MCP pour interroger la plateforme via Claude Code',
    'mcp-dev-secret-to-be-rotated-in-prod',
    [GrantTypeEnum::AUTHORIZATION_CODE, GrantTypeEnum::REFRESH_TOKEN],
    ['http://localhost:3742/callback']
);
$clientMcp->addSupportedScope(Scope::JEMARCHE_APP);
$clientMcp->setAskUserForAuthorization(false);
$manager->persist($clientMcp);
$this->setReference('client_mcp', $clientMcp);
```

- [ ] **Step 3 : Créer la migration Doctrine pour prod/staging**

```bash
bin/console doctrine:migrations:generate
```

Éditer le fichier généré dans `migrations/` :

```php
public function up(Schema $schema): void
{
    // Adapter l'UUID et le secret à la valeur réelle de prod
    $this->addSql(<<<'SQL'
        INSERT INTO oauth_clients (uuid, name, description, secret, grant_types, redirect_uris, scopes, ask_user_for_authorization, created_at, updated_at)
        VALUES (
            'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'Claude MCP',
            'Serveur MCP pour interroger la plateforme via Claude Code',
            'REPLACE_WITH_BCRYPT_HASH_IN_PROD',
            'authorization_code refresh_token',
            'http://localhost:3742/callback https://PROD_MCP_URL/callback',
            'jemarche_app',
            0,
            NOW(),
            NOW()
        )
    SQL);
}

public function down(Schema $schema): void
{
    $this->addSql("DELETE FROM oauth_clients WHERE uuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'");
}
```

- [ ] **Step 4 : Vérifier que la fixture charge bien**

```bash
php bin/console doctrine:fixtures:load --env=test --no-interaction 2>&1 | tail -5
```

Expected : `> loading App\DataFixtures\ORM\LoadClientData` sans erreur.

- [ ] **Step 5 : Commit**

```bash
git add src/DataFixtures/ORM/LoadClientData.php migrations/
git commit -m "feat(mcp): add MCP OAuth2 client fixture and migration"
```

---

### Task 2 : Méthode `getDailyStats()` dans AppHitRepository

**Contexte :** Les tendances de publications nécessitent des stats groupées par jour. `AppHitRepository::countImpressionAndOpenStats()` fait déjà la même chose sans grouper par date — s'en inspirer.

**Files :**
- Modify: `src/Repository/AppHitRepository.php`
- Test: `tests/Repository/AppHitRepositoryTest.php` (si existant) ou inline dans le controller test

- [ ] **Step 1 : Ajouter la méthode getDailyStats dans AppHitRepository**

```php
/**
 * @return array<array{day: string, unique_impressions: int, unique_opens: int, unique_clicks: int}>
 */
public function getDailyStats(
    TargetTypeEnum $type,
    UuidInterface $objectUuid,
    \DateTimeImmutable $since,
    \DateTimeImmutable $until,
): array {
    $conn = $this->getEntityManager()->getConnection();

    $sql = <<<'SQL'
        SELECT
            DATE(h.created_at) AS day,
            COUNT(DISTINCT IF(h.event_type = 'Impression', h.adherent_id, NULL)) AS unique_impressions,
            COUNT(DISTINCT IF(h.event_type = 'Open', h.adherent_id, NULL)) AS unique_opens,
            COUNT(DISTINCT IF(h.event_type = 'Click' AND h.suspicious = 0, h.adherent_id, NULL)) AS unique_clicks
        FROM app_hit h
        WHERE h.object_type = :object_type
          AND h.object_id = :object_id
          AND h.created_at BETWEEN :since AND :until
        GROUP BY DATE(h.created_at)
        ORDER BY day ASC
    SQL;

    $rows = $conn->executeQuery($sql, [
        'object_type' => $type->value,
        'object_id' => $objectUuid->toString(),
        'since' => $since->format('Y-m-d 00:00:00'),
        'until' => $until->format('Y-m-d 23:59:59'),
    ])->fetchAllAssociative();

    return array_map(static fn (array $row) => [
        'day' => $row['day'],
        'unique_impressions' => (int) $row['unique_impressions'],
        'unique_opens' => (int) $row['unique_opens'],
        'unique_clicks' => (int) $row['unique_clicks'],
    ], $rows);
}
```

- [ ] **Step 2 : Commit**

```bash
git add src/Repository/AppHitRepository.php
git commit -m "feat(mcp): add getDailyStats() to AppHitRepository"
```

---

### Task 3 : Endpoint `GET /api/v3/stats/publication/{uuid}/trends`

**Contexte :** Nouvelle route accrochée sur le contrôleur existant `GetHitStatsController`. La route `/{eventType}` existe déjà — ajouter `/trends` qui utilise `getDailyStats()`.

**Files :**
- Modify: `src/Controller/Api/GetHitStatsController.php`
- Create: `tests/Controller/Api/GetPublicationTrendsControllerTest.php`

- [ ] **Step 1 : Écrire le test fonctionnel qui échoue**

```php
<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadAdherentMessageData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class GetPublicationTrendsControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private ?string $accessToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_MCP_UUID,
            'mcp-dev-secret-to-be-rotated-in-prod',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            LoadAdherentData::ADHERENT_13_EMAIL, // utilisateur avec scope jemarche_app
            LoadAdherentData::DEFAULT_PASSWORD,
        );
    }

    public function testGetPublicationTrendsReturns200(): void
    {
        // Adapter avec un UUID de publication existant dans les fixtures
        $publicationUuid = LoadAdherentMessageData::MESSAGE1_UUID;

        $this->client->request(
            'GET',
            '/api/v3/stats/publication/'.$publicationUuid.'/trends',
            ['since' => '2020-01-01', 'until' => '2025-12-31'],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('period', $data);
        $this->assertArrayHasKey('data', $data);
        foreach ($data['data'] as $point) {
            $this->assertArrayHasKey('day', $point);
            $this->assertArrayHasKey('unique_impressions', $point);
            $this->assertArrayHasKey('unique_opens', $point);
            $this->assertArrayHasKey('unique_clicks', $point);
        }
    }

    public function testGetPublicationTrendsReturns403ForUnrelatedPublication(): void
    {
        $unrelatedUuid = LoadAdherentMessageData::MESSAGE_UNRELATED_UUID ?? '00000000-0000-0000-0000-000000000001';

        $this->client->request(
            'GET',
            '/api/v3/stats/publication/'.$unrelatedUuid.'/trends',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken]
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());
    }
}
```

- [ ] **Step 2 : Lancer le test — vérifier qu'il échoue**

```bash
php vendor/bin/phpunit tests/Controller/Api/GetPublicationTrendsControllerTest.php -v 2>&1 | tail -20
```

Expected : FAIL avec `404 Not Found` (route inexistante).

- [ ] **Step 3 : Implémenter la route /trends dans GetHitStatsController**

Ajouter dans `src/Controller/Api/GetHitStatsController.php` :

```php
#[Route('/{type}/{uuid}/trends', requirements: ['type' => 'publication', 'uuid' => '%pattern_uuid%'], methods: 'GET')]
public function getPublicationTrends(Request $request, string $uuid): Response
{
    if ($response = $this->checkAccess(TargetTypeEnum::Publication->value, $uuid)) {
        return $response;
    }

    $since = \DateTimeImmutable::createFromFormat('Y-m-d', $request->query->getString('since', (new \DateTime('-30 days'))->format('Y-m-d')));
    $until = \DateTimeImmutable::createFromFormat('Y-m-d', $request->query->getString('until', (new \DateTime())->format('Y-m-d')));

    if (!$since || !$until || $since > $until) {
        return $this->json(['error' => 'Invalid date range'], Response::HTTP_BAD_REQUEST);
    }

    return $this->json([
        'period' => ['since' => $since->format('Y-m-d'), 'until' => $until->format('Y-m-d')],
        'data' => $this->hitRepository->getDailyStats(TargetTypeEnum::Publication, Uuid::fromString($uuid), $since, $until),
    ]);
}
```

Note : la route `/trends` doit être déclarée AVANT `/{eventType}` dans le fichier pour que Symfony ne la matche pas comme eventType.

- [ ] **Step 4 : Lancer le test — vérifier qu'il passe**

```bash
php vendor/bin/phpunit tests/Controller/Api/GetPublicationTrendsControllerTest.php -v 2>&1 | tail -10
```

Expected : `OK (2 tests, X assertions)`.

- [ ] **Step 5 : Commit**

```bash
git add src/Controller/Api/GetHitStatsController.php src/Repository/AppHitRepository.php tests/Controller/Api/GetPublicationTrendsControllerTest.php
git commit -m "feat(mcp): add GET /v3/stats/publication/{uuid}/trends endpoint"
```

---

### Task 4 : Méthode `getAggregatedPublicationStats()` dans AdherentMessageRepository

**Contexte :** Stats globales sur toutes les publications visibles par l'utilisateur connecté. Agrège `publication_statistics` en jointure avec `adherent_messages` filtré par scope.

**Files :**
- Modify: `src/Repository/AdherentMessageRepository.php`

- [ ] **Step 1 : Ajouter la méthode dans AdherentMessageRepository**

```php
/**
 * @param string[] $messageUuids UUIDs des publications visibles par le scope courant
 */
public function getAggregatedPublicationStats(array $messageUuids, \DateTimeImmutable $since, \DateTimeImmutable $until): array
{
    if (empty($messageUuids)) {
        return $this->emptyAggregatedStats();
    }

    $qb = $this->createQueryBuilder('m')
        ->select('COUNT(DISTINCT m.id) AS total_publications')
        ->addSelect('COALESCE(SUM(s.contacts), 0) AS total_contacts')
        ->addSelect('COALESCE(SUM(s.uniqueOpens), 0) AS total_unique_opens')
        ->addSelect('COALESCE(SUM(s.uniqueClicks), 0) AS total_unique_clicks')
        ->addSelect('COALESCE(SUM(s.unsubscribed), 0) AS total_unsubscribed')
        ->addSelect('CAST(COALESCE(ROUND(SUM(s.uniqueOpens) * 100.0 / NULLIF(SUM(s.contacts), 0), 2), 0) AS DOUBLE) AS avg_open_rate')
        ->addSelect('CAST(COALESCE(ROUND(SUM(s.uniqueClicks) * 100.0 / NULLIF(SUM(s.uniqueOpens), 0), 2), 0) AS DOUBLE) AS avg_click_rate')
        ->leftJoin('m.statistics', 's')
        ->where('m.uuid IN (:uuids)')
        ->andWhere('m.sentAt BETWEEN :since AND :until')
        ->setParameters([
            'uuids' => $messageUuids,
            'since' => $since->format('Y-m-d 00:00:00'),
            'until' => $until->format('Y-m-d 23:59:59'),
        ])
    ;

    $result = $qb->getQuery()->getOneOrNullResult() ?? [];

    return [
        'total_publications' => (int) ($result['total_publications'] ?? 0),
        'total_contacts' => (int) ($result['total_contacts'] ?? 0),
        'total_unique_opens' => (int) ($result['total_unique_opens'] ?? 0),
        'total_unique_clicks' => (int) ($result['total_unique_clicks'] ?? 0),
        'total_unsubscribed' => (int) ($result['total_unsubscribed'] ?? 0),
        'avg_open_rate' => (float) ($result['avg_open_rate'] ?? 0.0),
        'avg_click_rate' => (float) ($result['avg_click_rate'] ?? 0.0),
    ];
}

private function emptyAggregatedStats(): array
{
    return [
        'total_publications' => 0,
        'total_contacts' => 0,
        'total_unique_opens' => 0,
        'total_unique_clicks' => 0,
        'total_unsubscribed' => 0,
        'avg_open_rate' => 0.0,
        'avg_click_rate' => 0.0,
    ];
}
```

- [ ] **Step 2 : Commit**

```bash
git add src/Repository/AdherentMessageRepository.php
git commit -m "feat(mcp): add getAggregatedPublicationStats() to AdherentMessageRepository"
```

---

### Task 5 : Endpoint `GET /api/v3/adherent_messages/statistics/aggregated`

**Files :**
- Create: `src/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsController.php`
- Create: `tests/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsControllerTest.php`

- [ ] **Step 1 : Écrire le test fonctionnel qui échoue**

```php
<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\AdherentMessage;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use App\OAuth\Model\Scope;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class GetPublicationAggregatedStatsControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    private ?string $accessToken = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_MCP_UUID,
            'mcp-dev-secret-to-be-rotated-in-prod',
            GrantTypeEnum::PASSWORD,
            Scope::JEMARCHE_APP,
            LoadAdherentData::ADHERENT_13_EMAIL,
            LoadAdherentData::DEFAULT_PASSWORD,
        );
    }

    public function testGetAggregatedStatsReturns200(): void
    {
        $this->client->request(
            'GET',
            '/api/v3/adherent_messages/statistics/aggregated',
            ['since' => '2020-01-01', 'until' => '2025-12-31'],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer '.$this->accessToken]
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('period', $data);
        $this->assertArrayHasKey('total_publications', $data);
        $this->assertArrayHasKey('total_contacts', $data);
        $this->assertArrayHasKey('avg_open_rate', $data);
        $this->assertArrayHasKey('avg_click_rate', $data);
    }

    public function testGetAggregatedStatsRequiresAuth(): void
    {
        $this->client->request('GET', '/api/v3/adherent_messages/statistics/aggregated');
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }
}
```

- [ ] **Step 2 : Lancer le test — vérifier qu'il échoue**

```bash
php vendor/bin/phpunit tests/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsControllerTest.php -v 2>&1 | tail -10
```

Expected : FAIL avec `404 Not Found`.

- [ ] **Step 3 : Créer le controller**

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Scope\ScopeGeneratorResolver;
use App\Security\Voter\PublicationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/v3/adherent_messages/statistics/aggregated', methods: ['GET'])]
#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
class GetPublicationAggregatedStatsController extends AbstractController
{
    public function __construct(
        private readonly AdherentMessageRepository $messageRepository,
        private readonly ScopeGeneratorResolver $resolver,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $since = \DateTimeImmutable::createFromFormat('Y-m-d', $request->query->getString('since', (new \DateTime('-30 days'))->format('Y-m-d')));
        $until = \DateTimeImmutable::createFromFormat('Y-m-d', $request->query->getString('until', (new \DateTime())->format('Y-m-d')));

        if (!$since || !$until || $since > $until) {
            return $this->json(['error' => 'Invalid date range'], Response::HTTP_BAD_REQUEST);
        }

        $scope = $this->resolver->generate();

        // Récupérer toutes les publications visibles pour ce scope
        $publications = $this->messageRepository->findBy([
            'source' => 'vox',
            'author' => $this->getUser(),
        ]);

        // Filtrer par droits du voter
        $accessibleUuids = array_map(
            static fn (AdherentMessage $m) => $m->getUuid()->toString(),
            array_filter($publications, fn (AdherentMessage $m) => $this->isGranted(PublicationVoter::PERMISSION, $m))
        );

        $stats = $this->messageRepository->getAggregatedPublicationStats($accessibleUuids, $since, $until);

        return $this->json(array_merge(
            ['period' => ['since' => $since->format('Y-m-d'), 'until' => $until->format('Y-m-d')]],
            $stats
        ));
    }
}
```

- [ ] **Step 4 : Lancer le test — vérifier qu'il passe**

```bash
php vendor/bin/phpunit tests/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsControllerTest.php -v 2>&1 | tail -10
```

Expected : `OK (2 tests, X assertions)`.

- [ ] **Step 5 : Commit**

```bash
git add src/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsController.php tests/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsControllerTest.php
git commit -m "feat(mcp): add GET /v3/adherent_messages/statistics/aggregated endpoint"
```

---

## Phase 2 — MCP Server (TypeScript)

### Task 6 : Scaffold du package MCP

**Files :**
- Create: `mcp/package.json`
- Create: `mcp/tsconfig.json`
- Create: `mcp/.env.example`

- [ ] **Step 1 : Créer la structure de base**

```bash
mkdir -p mcp/src/auth mcp/src/api mcp/src/tools mcp/tests/auth mcp/tests/tools
```

- [ ] **Step 2 : Créer mcp/package.json**

```json
{
  "name": "@plateforme/mcp-server",
  "version": "1.0.0",
  "description": "MCP server pour interroger la Plateforme via Claude Code",
  "type": "module",
  "bin": {
    "plateforme-mcp": "./dist/index.js"
  },
  "scripts": {
    "build": "tsc",
    "dev": "node --watch --experimental-transform-types src/index.ts",
    "start": "node dist/index.js",
    "test": "vitest run",
    "test:watch": "vitest"
  },
  "dependencies": {
    "@modelcontextprotocol/sdk": "^1.12.0",
    "open": "^10.1.0",
    "zod": "^3.24.0"
  },
  "devDependencies": {
    "@types/node": "^22.0.0",
    "typescript": "^5.8.0",
    "vitest": "^2.1.0"
  },
  "engines": {
    "node": ">=20"
  }
}
```

- [ ] **Step 3 : Créer mcp/tsconfig.json**

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "module": "NodeNext",
    "moduleResolution": "NodeNext",
    "outDir": "./dist",
    "rootDir": "./src",
    "strict": true,
    "esModuleInterop": true,
    "skipLibCheck": true,
    "declaration": true,
    "declarationMap": true,
    "sourceMap": true
  },
  "include": ["src/**/*"],
  "exclude": ["node_modules", "dist", "tests"]
}
```

- [ ] **Step 4 : Créer mcp/.env.example**

```bash
# URL de base de l'API Symfony (sans trailing slash)
PLATEFORME_API_URL=https://api.renaissance.fr

# OAuth2 client credentials
PLATEFORME_OAUTH_CLIENT_ID=a1b2c3d4-e5f6-7890-abcd-ef1234567890
PLATEFORME_OAUTH_CLIENT_SECRET=mcp-dev-secret-to-be-rotated-in-prod

# Port local pour le callback OAuth2
PLATEFORME_OAUTH_CALLBACK_PORT=3742
```

- [ ] **Step 5 : Installer les dépendances**

```bash
cd mcp && npm install
```

- [ ] **Step 6 : Commit**

```bash
git add mcp/
git commit -m "feat(mcp): scaffold MCP server package"
```

---

### Task 7 : Module PKCE et OAuth2

**Files :**
- Create: `mcp/src/auth/pkce.ts`
- Create: `mcp/src/auth/oauth.ts`
- Create: `mcp/tests/auth/pkce.test.ts`

- [ ] **Step 1 : Écrire les tests PKCE qui échouent**

Créer `mcp/tests/auth/pkce.test.ts` :

```typescript
import { describe, it, expect } from 'vitest';
import { generateVerifier, generateChallenge, generateState } from '../../src/auth/pkce.js';

describe('PKCE helpers', () => {
  it('generateVerifier returns a 43+ char base64url string', () => {
    const v = generateVerifier();
    expect(v).toMatch(/^[A-Za-z0-9\-_]+$/);
    expect(v.length).toBeGreaterThanOrEqual(43);
  });

  it('generateChallenge returns base64url SHA-256 of verifier', () => {
    const verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
    const challenge = generateChallenge(verifier);
    expect(challenge).toBe('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM');
  });

  it('generateState returns a non-empty string', () => {
    const s1 = generateState();
    const s2 = generateState();
    expect(s1.length).toBeGreaterThan(8);
    expect(s1).not.toBe(s2);
  });
});
```

- [ ] **Step 2 : Lancer les tests — vérifier qu'ils échouent**

```bash
cd mcp && npm test -- tests/auth/pkce.test.ts 2>&1 | tail -15
```

Expected : `Cannot find module '../../src/auth/pkce.js'`.

- [ ] **Step 3 : Créer mcp/src/auth/pkce.ts**

```typescript
import { createHash, randomBytes } from 'node:crypto';

export function generateVerifier(): string {
  return randomBytes(32).toString('base64url');
}

export function generateChallenge(verifier: string): string {
  return createHash('sha256').update(verifier).digest('base64url');
}

export function generateState(): string {
  return randomBytes(16).toString('hex');
}
```

- [ ] **Step 4 : Lancer les tests PKCE — vérifier qu'ils passent**

```bash
cd mcp && npm test -- tests/auth/pkce.test.ts 2>&1 | tail -10
```

Expected : `✓ PKCE helpers (3 tests)`.

- [ ] **Step 5 : Créer mcp/src/auth/oauth.ts**

```typescript
import { createServer, type IncomingMessage, type ServerResponse } from 'node:http';
import { generateChallenge, generateState, generateVerifier } from './pkce.js';

export interface OAuthConfig {
  apiUrl: string;
  clientId: string;
  clientSecret: string;
  callbackPort: number;
}

export interface TokenResponse {
  access_token: string;
  refresh_token?: string;
  expires_in: number;
  token_type: string;
}

export async function startAuthorizationFlow(config: OAuthConfig): Promise<TokenResponse> {
  const verifier = generateVerifier();
  const challenge = generateChallenge(verifier);
  const state = generateState();

  const authUrl = new URL('/oauth/v2/auth', config.apiUrl);
  authUrl.searchParams.set('response_type', 'code');
  authUrl.searchParams.set('client_id', config.clientId);
  authUrl.searchParams.set('redirect_uri', `http://localhost:${config.callbackPort}/callback`);
  authUrl.searchParams.set('scope', 'jemarche_app');
  authUrl.searchParams.set('code_challenge', challenge);
  authUrl.searchParams.set('code_challenge_method', 'S256');
  authUrl.searchParams.set('state', state);

  const { default: open } = await import('open');
  await open(authUrl.toString());
  process.stderr.write(`[MCP] Ouverture du navigateur pour l'authentification...\n`);

  const { code, receivedState } = await waitForCallback(config.callbackPort);

  if (receivedState !== state) {
    throw new Error('State mismatch — possible CSRF attack');
  }

  return exchangeCodeForToken(config, code, verifier);
}

export async function refreshAccessToken(config: OAuthConfig, refreshToken: string): Promise<TokenResponse> {
  const response = await fetch(`${config.apiUrl}/oauth/v2/token`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      grant_type: 'refresh_token',
      client_id: config.clientId,
      client_secret: config.clientSecret,
      refresh_token: refreshToken,
    }),
  });

  if (!response.ok) {
    throw new Error(`Token refresh failed: ${response.status}`);
  }

  return response.json() as Promise<TokenResponse>;
}

async function exchangeCodeForToken(config: OAuthConfig, code: string, verifier: string): Promise<TokenResponse> {
  const response = await fetch(`${config.apiUrl}/oauth/v2/token`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      grant_type: 'authorization_code',
      client_id: config.clientId,
      client_secret: config.clientSecret,
      code,
      redirect_uri: `http://localhost:${config.callbackPort}/callback`,
      code_verifier: verifier,
    }),
  });

  if (!response.ok) {
    const body = await response.text();
    throw new Error(`Token exchange failed: ${response.status} — ${body}`);
  }

  return response.json() as Promise<TokenResponse>;
}

function waitForCallback(port: number): Promise<{ code: string; receivedState: string }> {
  return new Promise((resolve, reject) => {
    const timeout = setTimeout(() => {
      server.close();
      reject(new Error('OAuth callback timeout (2 minutes)'));
    }, 120_000);

    const server = createServer((req: IncomingMessage, res: ServerResponse) => {
      const url = new URL(req.url ?? '/', `http://localhost:${port}`);
      const code = url.searchParams.get('code');
      const receivedState = url.searchParams.get('state');

      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(`
        <html><body style="font-family:sans-serif;text-align:center;padding:40px">
          <h1>✓ Authentification réussie</h1>
          <p>Vous pouvez fermer cet onglet et retourner dans Claude Code.</p>
        </body></html>
      `);

      server.close();
      clearTimeout(timeout);

      if (code && receivedState) {
        resolve({ code, receivedState });
      } else {
        reject(new Error('Missing code or state in callback'));
      }
    });

    server.listen(port, () => {
      process.stderr.write(`[MCP] En attente du callback OAuth2 sur localhost:${port}...\n`);
    });
  });
}
```

- [ ] **Step 6 : Commit**

```bash
git add mcp/src/auth/ mcp/tests/auth/
git commit -m "feat(mcp): OAuth2 PKCE flow + callback server"
```

---

### Task 8 : Token store + API client

**Files :**
- Create: `mcp/src/auth/token.ts`
- Create: `mcp/src/api/client.ts`
- Create: `mcp/tests/auth/token.test.ts`

- [ ] **Step 1 : Écrire les tests token store qui échouent**

```typescript
// mcp/tests/auth/token.test.ts
import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

// On override la config dir pour les tests
let tmpDir: string;

describe('TokenStore', () => {
  beforeEach(() => {
    tmpDir = mkdtempSync(join(tmpdir(), 'mcp-test-'));
    process.env.PLATEFORME_MCP_CONFIG_DIR = tmpDir;
  });

  afterEach(() => {
    rmSync(tmpDir, { recursive: true });
    delete process.env.PLATEFORME_MCP_CONFIG_DIR;
  });

  it('returns null when no token file exists', async () => {
    const { loadToken } = await import('../../src/auth/token.js');
    expect(loadToken()).toBeNull();
  });

  it('saves and loads a token', async () => {
    const { saveToken, loadToken } = await import('../../src/auth/token.js');
    const tokenData = {
      access_token: 'test-token',
      refresh_token: 'test-refresh',
      expires_at: Date.now() + 3600_000,
    };
    saveToken(tokenData);
    const loaded = loadToken();
    expect(loaded).toEqual(tokenData);
  });

  it('isTokenExpired returns true for expired token', async () => {
    const { isTokenExpired } = await import('../../src/auth/token.js');
    expect(isTokenExpired({ access_token: 'x', expires_at: Date.now() - 1000 })).toBe(true);
  });

  it('isTokenExpired returns false for valid token', async () => {
    const { isTokenExpired } = await import('../../src/auth/token.js');
    expect(isTokenExpired({ access_token: 'x', expires_at: Date.now() + 3600_000 })).toBe(false);
  });
});
```

- [ ] **Step 2 : Lancer les tests — vérifier qu'ils échouent**

```bash
cd mcp && npm test -- tests/auth/token.test.ts 2>&1 | tail -10
```

Expected : erreur module not found.

- [ ] **Step 3 : Créer mcp/src/auth/token.ts**

```typescript
import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'node:fs';
import { homedir } from 'node:os';
import { join } from 'node:path';

export interface StoredToken {
  access_token: string;
  refresh_token?: string;
  expires_at: number;
}

function getConfigDir(): string {
  return process.env.PLATEFORME_MCP_CONFIG_DIR ?? join(homedir(), '.config', 'plateforme-mcp');
}

function getTokenPath(): string {
  return join(getConfigDir(), 'token.json');
}

export function loadToken(): StoredToken | null {
  const path = getTokenPath();
  if (!existsSync(path)) return null;
  try {
    return JSON.parse(readFileSync(path, 'utf-8')) as StoredToken;
  } catch {
    return null;
  }
}

export function saveToken(token: StoredToken): void {
  const dir = getConfigDir();
  mkdirSync(dir, { recursive: true });
  writeFileSync(getTokenPath(), JSON.stringify(token, null, 2), 'utf-8');
}

export function clearToken(): void {
  const path = getTokenPath();
  if (existsSync(path)) {
    writeFileSync(path, '');
  }
}

export function isTokenExpired(token: Pick<StoredToken, 'expires_at'>): boolean {
  return Date.now() >= token.expires_at - 60_000; // 1 min de marge
}
```

- [ ] **Step 4 : Lancer les tests token — vérifier qu'ils passent**

```bash
cd mcp && npm test -- tests/auth/token.test.ts 2>&1 | tail -10
```

Expected : `✓ TokenStore (4 tests)`.

- [ ] **Step 5 : Créer mcp/src/api/client.ts**

```typescript
import { loadToken, saveToken, isTokenExpired } from '../auth/token.js';
import { refreshAccessToken, startAuthorizationFlow, type OAuthConfig } from '../auth/oauth.js';

export interface ApiClientConfig extends OAuthConfig {}

export interface ApiError {
  status: number;
  message: string;
}

export class ApiClient {
  constructor(private readonly config: ApiClientConfig) {}

  async get<T>(path: string, params?: Record<string, string | number | boolean | undefined>): Promise<T> {
    const token = await this.getValidToken();
    const url = new URL(path, this.config.apiUrl);

    if (params) {
      for (const [key, value] of Object.entries(params)) {
        if (value !== undefined && value !== null) {
          url.searchParams.set(key, String(value));
        }
      }
    }

    const response = await fetch(url.toString(), {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      const body = await response.text().catch(() => '');
      throw new ApiError(response.status, body);
    }

    return response.json() as Promise<T>;
  }

  private async getValidToken(): Promise<string> {
    let stored = loadToken();

    if (stored && !isTokenExpired(stored)) {
      return stored.access_token;
    }

    if (stored?.refresh_token) {
      try {
        const refreshed = await refreshAccessToken(this.config, stored.refresh_token);
        stored = {
          access_token: refreshed.access_token,
          refresh_token: refreshed.refresh_token ?? stored.refresh_token,
          expires_at: Date.now() + refreshed.expires_in * 1000,
        };
        saveToken(stored);
        return stored.access_token;
      } catch {
        // refresh échoué → re-authenticate
      }
    }

    // Premier lancement ou token expiré sans refresh
    const tokenResponse = await startAuthorizationFlow(this.config);
    const newStored = {
      access_token: tokenResponse.access_token,
      refresh_token: tokenResponse.refresh_token,
      expires_at: Date.now() + tokenResponse.expires_in * 1000,
    };
    saveToken(newStored);
    return newStored.access_token;
  }
}

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    message: string,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}
```

- [ ] **Step 6 : Commit**

```bash
git add mcp/src/auth/token.ts mcp/src/api/client.ts mcp/tests/auth/token.test.ts
git commit -m "feat(mcp): token store + API client with auto-refresh"
```

---

### Task 9 : Outils Users/Contacts

**Files :**
- Create: `mcp/src/tools/users.ts`
- Create: `mcp/tests/tools/users.test.ts`

- [ ] **Step 1 : Écrire les tests qui échouent**

```typescript
// mcp/tests/tools/users.test.ts
import { describe, it, expect, vi } from 'vitest';

const mockGet = vi.fn();
vi.mock('../../src/api/client.js', () => ({
  ApiClient: vi.fn().mockImplementation(() => ({ get: mockGet })),
}));

describe('listUsers tool', () => {
  it('calls /api/v3/adherents with filters', async () => {
    const { buildListUsersParams } = await import('../../src/tools/users.js');

    const params = buildListUsersParams({
      searchTerm: 'Jean',
      adherentTags: 'adherent:a_jour_2026',
      page: 2,
      page_size: 50,
    });

    expect(params).toMatchObject({
      search_term: 'Jean',
      adherent_tags: 'adherent:a_jour_2026',
      page: 2,
      page_size: 50,
    });
  });

  it('omits undefined filters', async () => {
    const { buildListUsersParams } = await import('../../src/tools/users.js');
    const params = buildListUsersParams({ page: 1 });
    expect(Object.keys(params)).not.toContain('search_term');
  });
});
```

- [ ] **Step 2 : Lancer les tests — vérifier qu'ils échouent**

```bash
cd mcp && npm test -- tests/tools/users.test.ts 2>&1 | tail -10
```

Expected : module not found.

- [ ] **Step 3 : Créer mcp/src/tools/users.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

const ListUsersInput = z.object({
  searchTerm: z.string().optional().describe('Recherche textuelle (nom, prénom, email)'),
  firstName: z.string().optional(),
  lastName: z.string().optional(),
  gender: z.enum(['male', 'female']).optional(),
  ageStart: z.number().int().min(0).max(120).optional().describe('Âge minimum'),
  ageEnd: z.number().int().min(0).max(120).optional().describe('Âge maximum'),
  city: z.string().optional().describe('Ville(s), séparées par virgule'),
  adherentTags: z.string().optional().describe('Tags adhérent ex: adherent:a_jour_2026, sympathisant'),
  electTags: z.string().optional().describe('Tags élus ex: elu, elu:cotisation_ok'),
  staticTags: z.string().optional().describe('Labels statiques (codes)'),
  isCommitteeMember: z.boolean().optional(),
  emailSubscription: z.boolean().optional(),
  smsSubscription: z.boolean().optional(),
  isCertified: z.boolean().optional(),
  zones: z.string().optional().describe('UUIDs de zones séparés par virgule'),
  committeeUuids: z.string().optional().describe('UUIDs de comités séparés par virgule'),
  registeredStart: z.string().optional().describe('Date inscription min (YYYY-MM-DD)'),
  registeredEnd: z.string().optional().describe('Date inscription max (YYYY-MM-DD)'),
  firstMembershipStart: z.string().optional().describe('Date 1ère adhésion min (YYYY-MM-DD)'),
  firstMembershipEnd: z.string().optional().describe('Date 1ère adhésion max (YYYY-MM-DD)'),
  sort: z.enum(['createdAt', 'lastName']).optional(),
  order: z.enum(['a', 'd']).optional().describe('a = asc, d = desc'),
  page: z.number().int().min(1).default(1),
  page_size: z.number().int().min(1).max(200).default(50),
});

export type ListUsersInputType = z.infer<typeof ListUsersInput>;

export function buildListUsersParams(input: Partial<ListUsersInputType>): Record<string, string | number | boolean | undefined> {
  return {
    search_term: input.searchTerm,
    first_name: input.firstName,
    last_name: input.lastName,
    gender: input.gender,
    'age[start]': input.ageStart,
    'age[end]': input.ageEnd,
    city: input.city,
    adherent_tags: input.adherentTags,
    elect_tags: input.electTags,
    static_tags: input.staticTags,
    is_committee_member: input.isCommitteeMember,
    email_subscription: input.emailSubscription,
    sms_subscription: input.smsSubscription,
    is_certified: input.isCertified,
    'registered[start]': input.registeredStart,
    'registered[end]': input.registeredEnd,
    'first_membership[start]': input.firstMembershipStart,
    'first_membership[end]': input.firstMembershipEnd,
    sort: input.sort,
    order: input.order,
    page: input.page ?? 1,
    page_size: input.page_size ?? 50,
  };
}

export function createUserTools(client: ApiClient): Tool[] {
  return [
    {
      name: 'list_users',
      description: 'Liste les utilisateurs/contacts de la plateforme. Utiliser adherentTags pour filtrer par type : "adherent:a_jour_2026" = adhérents à jour, "sympathisant" = sympathisants, "elu" = élus.',
      inputSchema: {
        type: 'object' as const,
        properties: ListUsersInput.shape as Record<string, unknown>,
      },
    },
    {
      name: 'get_user',
      description: "Récupère le détail d'un utilisateur par son UUID.",
      inputSchema: {
        type: 'object' as const,
        properties: { uuid: { type: 'string', description: 'UUID de l\'utilisateur' } },
        required: ['uuid'],
      },
    },
    {
      name: 'count_users',
      description: 'Compte les adhérents et sympathisants. Paramètre optionnel : since (timestamp UNIX) pour compter depuis une date.',
      inputSchema: {
        type: 'object' as const,
        properties: { since: { type: 'number', description: 'Timestamp UNIX optionnel' } },
      },
    },
  ];
}

export async function handleUserTool(
  toolName: string,
  args: Record<string, unknown>,
  client: ApiClient,
): Promise<unknown> {
  switch (toolName) {
    case 'list_users': {
      const input = ListUsersInput.parse(args);
      return client.get('/api/v3/adherents', buildListUsersParams(input));
    }
    case 'get_user': {
      const { uuid } = z.object({ uuid: z.string().uuid() }).parse(args);
      return client.get(`/api/v3/adherents/${uuid}`);
    }
    case 'count_users': {
      const { since } = z.object({ since: z.number().optional() }).parse(args);
      return client.get('/api/v3/adherents/count', since ? { since } : undefined);
    }
    default:
      throw new Error(`Unknown user tool: ${toolName}`);
  }
}
```

- [ ] **Step 4 : Lancer les tests — vérifier qu'ils passent**

```bash
cd mcp && npm test -- tests/tools/users.test.ts 2>&1 | tail -10
```

Expected : `✓ listUsers tool (2 tests)`.

- [ ] **Step 5 : Commit**

```bash
git add mcp/src/tools/users.ts mcp/tests/tools/users.test.ts
git commit -m "feat(mcp): user tools (list_users, get_user, count_users)"
```

---

### Task 10 : Outils Publications

**Files :**
- Create: `mcp/src/tools/publications.ts`
- Create: `mcp/tests/tools/publications.test.ts`

- [ ] **Step 1 : Écrire les tests qui échouent**

```typescript
// mcp/tests/tools/publications.test.ts
import { describe, it, expect, vi } from 'vitest';

describe('publications tools', () => {
  it('list_publications builds correct params', async () => {
    const { buildListPublicationsParams } = await import('../../src/tools/publications.js');
    const params = buildListPublicationsParams({ label: 'test', page: 1, page_size: 10 });
    expect(params).toMatchObject({ label: 'test', page: 1, page_size: 10 });
  });

  it('list_publications adds source=vox filter', async () => {
    const { buildListPublicationsParams } = await import('../../src/tools/publications.js');
    const params = buildListPublicationsParams({});
    expect(params.source).toBe('vox');
  });
});
```

- [ ] **Step 2 : Lancer les tests — vérifier qu'ils échouent**

```bash
cd mcp && npm test -- tests/tools/publications.test.ts 2>&1 | tail -10
```

- [ ] **Step 3 : Créer mcp/src/tools/publications.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

const ListPublicationsInput = z.object({
  label: z.string().optional().describe('Filtrer par titre/label'),
  status: z.string().optional().describe('Statut : draft, sent, scheduled'),
  sort: z.string().optional().describe('Champ de tri (ex: createdAt)'),
  order: z.enum(['asc', 'desc']).optional(),
  since: z.string().optional().describe('Publications envoyées depuis (YYYY-MM-DD)'),
  until: z.string().optional().describe('Publications envoyées jusqu\'au (YYYY-MM-DD)'),
  page: z.number().int().min(1).default(1),
  page_size: z.number().int().min(1).max(100).default(20),
});

export function buildListPublicationsParams(input: Partial<z.infer<typeof ListPublicationsInput>>): Record<string, string | number | undefined> {
  return {
    source: 'vox',
    label: input.label,
    status: input.status,
    sort: input.sort,
    order: input.order,
    since: input.since,
    until: input.until,
    page: input.page ?? 1,
    page_size: input.page_size ?? 20,
  };
}

export function createPublicationTools(client: ApiClient): Tool[] {
  return [
    {
      name: 'list_publications',
      description: 'Liste les publications (messages Vox). Chaque publication inclut ses statistiques (opens, clicks, reach).',
      inputSchema: {
        type: 'object' as const,
        properties: ListPublicationsInput.shape as Record<string, unknown>,
      },
    },
    {
      name: 'get_publication',
      description: "Récupère le détail d'une publication avec ses statistiques complètes.",
      inputSchema: {
        type: 'object' as const,
        properties: { uuid: { type: 'string' } },
        required: ['uuid'],
      },
    },
    {
      name: 'get_publication_stats',
      description: "Retourne les statistiques d'engagement détaillées d'une publication (impressions, opens, clicks par canal).",
      inputSchema: {
        type: 'object' as const,
        properties: { uuid: { type: 'string' } },
        required: ['uuid'],
      },
    },
    {
      name: 'get_publication_trends',
      description: "Retourne les statistiques jour par jour d'une publication sur une période donnée.",
      inputSchema: {
        type: 'object' as const,
        properties: {
          uuid: { type: 'string' },
          since: { type: 'string', description: 'YYYY-MM-DD, défaut: -30 jours' },
          until: { type: 'string', description: 'YYYY-MM-DD, défaut: aujourd\'hui' },
        },
        required: ['uuid'],
      },
    },
    {
      name: 'get_publications_kpi',
      description: 'Retourne les KPI agrégés des publications pour le scope courant (open rate, click rate, unsub rate) sur une période glissante.',
      inputSchema: {
        type: 'object' as const,
        properties: {
          max_history: { type: 'number', description: 'Nombre de jours, défaut: 30' },
        },
      },
    },
    {
      name: 'get_publications_aggregated',
      description: 'Retourne les stats globales sur toutes les publications du scope (total envois, opens, clicks, taux moyens).',
      inputSchema: {
        type: 'object' as const,
        properties: {
          since: { type: 'string', description: 'YYYY-MM-DD' },
          until: { type: 'string', description: 'YYYY-MM-DD' },
        },
      },
    },
  ];
}

export async function handlePublicationTool(
  toolName: string,
  args: Record<string, unknown>,
  client: ApiClient,
): Promise<unknown> {
  switch (toolName) {
    case 'list_publications': {
      const input = ListPublicationsInput.parse(args);
      return client.get('/api/v3/adherent_messages', buildListPublicationsParams(input));
    }
    case 'get_publication': {
      const { uuid } = z.object({ uuid: z.string().uuid() }).parse(args);
      return client.get(`/api/v3/adherent_messages/${uuid}`);
    }
    case 'get_publication_stats': {
      const { uuid } = z.object({ uuid: z.string().uuid() }).parse(args);
      return client.get(`/api/v3/stats/publication/${uuid}`);
    }
    case 'get_publication_trends': {
      const { uuid, since, until } = z.object({
        uuid: z.string().uuid(),
        since: z.string().optional(),
        until: z.string().optional(),
      }).parse(args);
      return client.get(`/api/v3/stats/publication/${uuid}/trends`, { since, until });
    }
    case 'get_publications_kpi': {
      const { max_history } = z.object({ max_history: z.number().optional() }).parse(args);
      return client.get('/api/v3/adherent_messages/kpi', max_history ? { max_history } : undefined);
    }
    case 'get_publications_aggregated': {
      const { since, until } = z.object({ since: z.string().optional(), until: z.string().optional() }).parse(args);
      return client.get('/api/v3/adherent_messages/statistics/aggregated', { since, until });
    }
    default:
      throw new Error(`Unknown publication tool: ${toolName}`);
  }
}
```

- [ ] **Step 4 : Lancer les tests — vérifier qu'ils passent**

```bash
cd mcp && npm test -- tests/tools/publications.test.ts 2>&1 | tail -10
```

Expected : `✓ publications tools (2 tests)`.

- [ ] **Step 5 : Commit**

```bash
git add mcp/src/tools/publications.ts mcp/tests/tools/publications.test.ts
git commit -m "feat(mcp): publication tools (list, get, stats, kpi, trends, aggregated)"
```

---

### Task 11 : Outils Events, Actions, Committees, Zones, NationalEvents

**Files :**
- Create: `mcp/src/tools/events.ts`
- Create: `mcp/src/tools/actions.ts`
- Create: `mcp/src/tools/committees.ts`
- Create: `mcp/src/tools/zones.ts`
- Create: `mcp/src/tools/national-events.ts`

- [ ] **Step 1 : Créer mcp/src/tools/events.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

export function createEventTools(): Tool[] {
  return [
    {
      name: 'list_events',
      description: 'Liste les événements. Filtrable par nom, ville, statut, dates.',
      inputSchema: {
        type: 'object' as const,
        properties: {
          name: { type: 'string', description: 'Filtrer par nom (recherche partielle)' },
          city: { type: 'string' },
          status: { type: 'string', description: 'scheduled, cancelled, passed' },
          since: { type: 'string', description: 'YYYY-MM-DD' },
          until: { type: 'string', description: 'YYYY-MM-DD' },
          page: { type: 'number', default: 1 },
          page_size: { type: 'number', default: 20 },
        },
      },
    },
    {
      name: 'get_event',
      description: "Récupère le détail d'un événement par UUID.",
      inputSchema: {
        type: 'object' as const,
        properties: { uuid: { type: 'string' } },
        required: ['uuid'],
      },
    },
  ];
}

export async function handleEventTool(toolName: string, args: Record<string, unknown>, client: ApiClient): Promise<unknown> {
  switch (toolName) {
    case 'list_events': {
      const input = z.object({
        name: z.string().optional(),
        city: z.string().optional(),
        status: z.string().optional(),
        since: z.string().optional(),
        until: z.string().optional(),
        page: z.number().default(1),
        page_size: z.number().default(20),
      }).parse(args);
      return client.get('/api/v3/events', input as Record<string, string | number | undefined>);
    }
    case 'get_event': {
      const { uuid } = z.object({ uuid: z.string().uuid() }).parse(args);
      return client.get(`/api/v3/events/${uuid}`);
    }
    default:
      throw new Error(`Unknown event tool: ${toolName}`);
  }
}
```

- [ ] **Step 2 : Créer mcp/src/tools/actions.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

export function createActionTools(): Tool[] {
  return [
    {
      name: 'list_actions',
      description: 'Liste les actions terrain.',
      inputSchema: {
        type: 'object' as const,
        properties: {
          page: { type: 'number', default: 1 },
          page_size: { type: 'number', default: 20 },
        },
      },
    },
    {
      name: 'get_action',
      description: "Récupère le détail d'une action par UUID.",
      inputSchema: {
        type: 'object' as const,
        properties: { uuid: { type: 'string' } },
        required: ['uuid'],
      },
    },
  ];
}

export async function handleActionTool(toolName: string, args: Record<string, unknown>, client: ApiClient): Promise<unknown> {
  switch (toolName) {
    case 'list_actions': {
      const input = z.object({ page: z.number().default(1), page_size: z.number().default(20) }).parse(args);
      return client.get('/api/v3/actions', input as Record<string, number>);
    }
    case 'get_action': {
      const { uuid } = z.object({ uuid: z.string().uuid() }).parse(args);
      return client.get(`/api/v3/actions/${uuid}`);
    }
    default:
      throw new Error(`Unknown action tool: ${toolName}`);
  }
}
```

- [ ] **Step 3 : Créer mcp/src/tools/committees.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

export function createCommitteeTools(): Tool[] {
  return [
    {
      name: 'list_committees',
      description: 'Liste les comités.',
      inputSchema: {
        type: 'object' as const,
        properties: {
          page: { type: 'number', default: 1 },
          page_size: { type: 'number', default: 20 },
        },
      },
    },
    {
      name: 'get_committee',
      description: "Récupère le détail d'un comité par UUID.",
      inputSchema: {
        type: 'object' as const,
        properties: { uuid: { type: 'string' } },
        required: ['uuid'],
      },
    },
  ];
}

export async function handleCommitteeTool(toolName: string, args: Record<string, unknown>, client: ApiClient): Promise<unknown> {
  switch (toolName) {
    case 'list_committees': {
      const input = z.object({ page: z.number().default(1), page_size: z.number().default(20) }).parse(args);
      return client.get('/api/v3/committees', input as Record<string, number>);
    }
    case 'get_committee': {
      const { uuid } = z.object({ uuid: z.string().uuid() }).parse(args);
      return client.get(`/api/v3/committees/${uuid}`);
    }
    default:
      throw new Error(`Unknown committee tool: ${toolName}`);
  }
}
```

- [ ] **Step 4 : Créer mcp/src/tools/zones.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

export function createZoneTools(): Tool[] {
  return [
    {
      name: 'list_zones',
      description: 'Liste les zones géographiques. Types : country, region, department, district, city, borough.',
      inputSchema: {
        type: 'object' as const,
        properties: {
          name: { type: 'string', description: 'Filtrer par nom' },
          type: { type: 'string', description: 'Type de zone' },
          page: { type: 'number', default: 1 },
          page_size: { type: 'number', default: 50 },
        },
      },
    },
  ];
}

export async function handleZoneTool(toolName: string, args: Record<string, unknown>, client: ApiClient): Promise<unknown> {
  if (toolName !== 'list_zones') throw new Error(`Unknown zone tool: ${toolName}`);
  const input = z.object({
    name: z.string().optional(),
    type: z.string().optional(),
    page: z.number().default(1),
    page_size: z.number().default(50),
  }).parse(args);
  return client.get('/api/v3/zones', input as Record<string, string | number | undefined>);
}
```

- [ ] **Step 5 : Créer mcp/src/tools/national-events.ts**

```typescript
import { z } from 'zod';
import type { ApiClient } from '../api/client.js';
import type { Tool } from '@modelcontextprotocol/sdk/types.js';

export function createNationalEventTools(): Tool[] {
  return [
    {
      name: 'list_national_events',
      description: 'Liste les événements nationaux.',
      inputSchema: {
        type: 'object' as const,
        properties: {
          page: { type: 'number', default: 1 },
          page_size: { type: 'number', default: 20 },
        },
      },
    },
  ];
}

export async function handleNationalEventTool(toolName: string, args: Record<string, unknown>, client: ApiClient): Promise<unknown> {
  if (toolName !== 'list_national_events') throw new Error(`Unknown national_event tool: ${toolName}`);
  const input = z.object({ page: z.number().default(1), page_size: z.number().default(20) }).parse(args);
  return client.get('/api/v3/national_events', input as Record<string, number>);
}
```

- [ ] **Step 6 : Commit**

```bash
git add mcp/src/tools/events.ts mcp/src/tools/actions.ts mcp/src/tools/committees.ts mcp/src/tools/zones.ts mcp/src/tools/national-events.ts
git commit -m "feat(mcp): events, actions, committees, zones, national-events tools"
```

---

### Task 12 : MCP Server + Entry point

**Files :**
- Create: `mcp/src/server.ts`
- Create: `mcp/src/index.ts`

- [ ] **Step 1 : Créer mcp/src/server.ts**

```typescript
import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { CallToolRequestSchema, ListToolsRequestSchema } from '@modelcontextprotocol/sdk/types.js';
import { ApiClient } from './api/client.js';
import { createUserTools, handleUserTool } from './tools/users.js';
import { createPublicationTools, handlePublicationTool } from './tools/publications.js';
import { createEventTools, handleEventTool } from './tools/events.js';
import { createActionTools, handleActionTool } from './tools/actions.js';
import { createCommitteeTools, handleCommitteeTool } from './tools/committees.js';
import { createZoneTools, handleZoneTool } from './tools/zones.js';
import { createNationalEventTools, handleNationalEventTool } from './tools/national-events.js';

export function createMcpServer(client: ApiClient): Server {
  const server = new Server(
    { name: 'plateforme-mcp', version: '1.0.0' },
    { capabilities: { tools: {} } },
  );

  const allTools = [
    ...createUserTools(client),
    ...createPublicationTools(client),
    ...createEventTools(),
    ...createActionTools(),
    ...createCommitteeTools(),
    ...createZoneTools(),
    ...createNationalEventTools(),
  ];

  server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools: allTools }));

  server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const { name, arguments: args } = request.params;
    const toolArgs = (args ?? {}) as Record<string, unknown>;

    try {
      let result: unknown;

      if (['list_users', 'get_user', 'count_users'].includes(name)) {
        result = await handleUserTool(name, toolArgs, client);
      } else if (['list_publications', 'get_publication', 'get_publication_stats', 'get_publication_trends', 'get_publications_kpi', 'get_publications_aggregated'].includes(name)) {
        result = await handlePublicationTool(name, toolArgs, client);
      } else if (['list_events', 'get_event'].includes(name)) {
        result = await handleEventTool(name, toolArgs, client);
      } else if (['list_actions', 'get_action'].includes(name)) {
        result = await handleActionTool(name, toolArgs, client);
      } else if (['list_committees', 'get_committee'].includes(name)) {
        result = await handleCommitteeTool(name, toolArgs, client);
      } else if (name === 'list_zones') {
        result = await handleZoneTool(name, toolArgs, client);
      } else if (name === 'list_national_events') {
        result = await handleNationalEventTool(name, toolArgs, client);
      } else {
        return { content: [{ type: 'text', text: `Unknown tool: ${name}` }], isError: true };
      }

      return { content: [{ type: 'text', text: JSON.stringify(result, null, 2) }] };
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      return { content: [{ type: 'text', text: `Error: ${message}` }], isError: true };
    }
  });

  return server;
}
```

- [ ] **Step 2 : Créer mcp/src/index.ts**

```typescript
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import { ApiClient } from './api/client.js';
import { createMcpServer } from './server.js';

async function main(): Promise<void> {
  const apiUrl = process.env.PLATEFORME_API_URL;
  const clientId = process.env.PLATEFORME_OAUTH_CLIENT_ID;
  const clientSecret = process.env.PLATEFORME_OAUTH_CLIENT_SECRET;
  const callbackPort = Number(process.env.PLATEFORME_OAUTH_CALLBACK_PORT ?? '3742');

  if (!apiUrl || !clientId || !clientSecret) {
    process.stderr.write('[MCP] Missing required env vars: PLATEFORME_API_URL, PLATEFORME_OAUTH_CLIENT_ID, PLATEFORME_OAUTH_CLIENT_SECRET\n');
    process.exit(1);
  }

  const client = new ApiClient({ apiUrl, clientId, clientSecret, callbackPort });
  const server = createMcpServer(client);
  const transport = new StdioServerTransport();

  await server.connect(transport);
  process.stderr.write('[MCP] Plateforme MCP server started (stdio)\n');
}

main().catch((err) => {
  process.stderr.write(`[MCP] Fatal error: ${err instanceof Error ? err.message : String(err)}\n`);
  process.exit(1);
});
```

- [ ] **Step 3 : Builder le projet**

```bash
cd mcp && npm run build 2>&1 | tail -20
```

Expected : `dist/` généré sans erreur.

- [ ] **Step 4 : Vérifier que le serveur démarre**

```bash
cd mcp && PLATEFORME_API_URL=https://api.renaissance.fr PLATEFORME_OAUTH_CLIENT_ID=test PLATEFORME_OAUTH_CLIENT_SECRET=test node dist/index.js 2>&1 &
sleep 1 && kill %1
```

Expected : `[MCP] Plateforme MCP server started (stdio)` dans stderr.

- [ ] **Step 5 : Commit**

```bash
git add mcp/src/server.ts mcp/src/index.ts
git commit -m "feat(mcp): MCP server wiring + stdio entry point"
```

---

### Task 13 : Configuration Claude Code + Documentation

**Files :**
- Create: `mcp/.claude.mcp.json` (ou `.mcp.json` à la racine)
- Create: `mcp/README.md`

- [ ] **Step 1 : Créer le fichier de config MCP pour Claude Code**

Créer `.mcp.json` à la racine du projet :

```json
{
  "mcpServers": {
    "plateforme": {
      "command": "node",
      "args": ["mcp/dist/index.js"],
      "env": {
        "PLATEFORME_API_URL": "https://api.renaissance.fr",
        "PLATEFORME_OAUTH_CLIENT_ID": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "PLATEFORME_OAUTH_CLIENT_SECRET": "REPLACE_WITH_REAL_SECRET"
      }
    },
    "plateforme-staging": {
      "command": "node",
      "args": ["mcp/dist/index.js"],
      "env": {
        "PLATEFORME_API_URL": "https://staging.api.renaissance.fr",
        "PLATEFORME_OAUTH_CLIENT_ID": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
        "PLATEFORME_OAUTH_CLIENT_SECRET": "REPLACE_WITH_STAGING_SECRET"
      }
    }
  }
}
```

- [ ] **Step 2 : Ajouter .mcp.json dans .gitignore (contient des secrets)**

```bash
echo ".mcp.json" >> .gitignore
echo "mcp/dist/" >> .gitignore
```

Créer `.mcp.json.example` avec les valeurs de template (sans secrets) et le committer à la place.

- [ ] **Step 3 : Lancer la suite de tests complète**

```bash
cd mcp && npm test 2>&1 | tail -20
```

Expected : tous les tests passent.

```bash
php vendor/bin/phpunit tests/Controller/Api/GetPublicationTrendsControllerTest.php tests/Controller/Api/AdherentMessage/GetPublicationAggregatedStatsControllerTest.php -v 2>&1 | tail -10
```

Expected : `OK (4 tests)`.

- [ ] **Step 4 : Commit final**

```bash
git add .gitignore .mcp.json.example mcp/
git commit -m "feat(mcp): Claude Code config + .mcp.json.example"
```

---

## Récapitulatif — Effort par phase

| Phase | Tasks | Effort estimé |
|---|---|---|
| Phase 1 — Symfony | Tasks 1-5 | 2-3 jours |
| Phase 2 — MCP Server | Tasks 6-13 | 4-5 jours |
| **Total** | | **~8 jours** |

## Dépendances entre tasks

```
Task 1 (client OAuth2) ← prérequis pour tous les tests PHP
Task 2 (getDailyStats) ← prérequis Task 3
Task 4 (getAggregatedStats) ← prérequis Task 5
Tasks 6-7-8 (auth + client) ← prérequis Tasks 9-10-11
Tasks 9-10-11 (tools) ← prérequis Task 12
```

Tasks 3 et 5 (Symfony) sont indépendantes entre elles.
Tasks 9, 10, 11 (tools MCP) sont indépendantes entre elles — parallélisables.

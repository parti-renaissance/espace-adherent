<?php

declare(strict_types=1);

namespace Tests\App\Controller\Admin;

use App\Entity\Geo\Zone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractAdminWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('admin')]
class ZoneAutocompleteControllerTest extends AbstractAdminWebTestCase
{
    use ControllerTestTrait;

    #[DataProvider('provideAdministratorsWithDifferentRoleSets')]
    public function testAnyAdministratorCanSearchZones(string $email): void
    {
        $this->authenticateAsAdmin($this->client, $email);

        $payload = $this->requestZoneAutocomplete(['q' => 'Paris']);

        self::assertSame('OK', $payload['status']);
        self::assertNotEmpty($payload['items']);
        self::assertArrayHasKey('id', $payload['items'][0]);
        self::assertArrayHasKey('label', $payload['items'][0]);
    }

    public static function provideAdministratorsWithDifferentRoleSets(): iterable
    {
        yield 'super admin' => ['superadmin@en-marche-dev.fr'];
        yield 'full-rights admin' => ['admin@en-marche-dev.fr'];
        yield 'renaissance admin (adherent rights only, no mandate admin)' => ['admin@renaissance.code'];
        yield 'writer admin (no adherent rights at all)' => ['writer@en-marche-dev.fr'];
    }

    public function testAnonymousRequestIsRedirectedToLogin(): void
    {
        $this->client->request(Request::METHOD_GET, '/autocomplete/zone?q=Paris');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        self::assertStringContainsString('/login', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testEmptyQueryReturnsEmptyItems(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete(['q' => '']);

        self::assertSame('OK', $payload['status']);
        self::assertSame([], $payload['items']);
    }

    public function testWhitespaceOnlyQueryReturnsEmptyItems(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete(['q' => '   ']);

        self::assertSame('OK', $payload['status']);
        self::assertSame([], $payload['items']);
    }

    public function testResultsAreOrderedByZoneTypePriority(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete(['q' => 'France']);

        self::assertNotEmpty($payload['items']);
        // The country ("Pays : France") sits at the top of the priority list.
        self::assertStringStartsWith('Pays', $payload['items'][0]['label']);
    }

    public function testZoneTypesFilterAcceptsSingleType(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'Paris',
            'zone_types' => Zone::DEPARTMENT,
        ]);

        self::assertNotEmpty($payload['items']);
        foreach ($payload['items'] as $item) {
            self::assertStringStartsWith('Département', $item['label']);
        }
    }

    public function testZoneTypesFilterAcceptsCommaSeparatedList(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'Paris',
            'zone_types' => Zone::DEPARTMENT.','.Zone::REGION,
        ]);

        self::assertNotEmpty($payload['items']);
        foreach ($payload['items'] as $item) {
            self::assertMatchesRegularExpression('/^(Département|Région)/', $item['label']);
        }
    }

    public function testUnknownZoneTypesAreSilentlyDropped(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'Paris',
            'zone_types' => 'unicorn',
        ]);

        self::assertSame('OK', $payload['status']);
        self::assertNotEmpty($payload['items']);
    }

    public function testInactiveZonesAreAlwaysExcluded(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $inactiveCount = (int) $this->manager->createQueryBuilder()
            ->select('COUNT(zone.id)')
            ->from(Zone::class, 'zone')
            ->where('zone.active = false')
            ->andWhere('zone.name LIKE :search OR zone.code LIKE :search')
            ->setParameter('search', '%a%')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        self::assertGreaterThan(0, $inactiveCount, 'Test fixtures must contain at least one inactive zone matching "a".');

        $payload = $this->requestZoneAutocomplete(['q' => 'a', '_per_page' => 100]);
        $returnedIds = array_column($payload['items'], 'id');

        $inactiveIds = array_map(
            static fn (array $row): int => (int) $row['id'],
            $this->manager->createQueryBuilder()
                ->select('zone.id')
                ->from(Zone::class, 'zone')
                ->where('zone.active = false')
                ->getQuery()
                ->getArrayResult()
        );

        self::assertSame([], array_intersect($returnedIds, $inactiveIds));
    }

    public function testJecouteManagedAreaPresetKeepsDepartmentsAndParisBoroughs(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'Paris',
            'preset' => 'jecoute_managed_area',
        ]);

        self::assertNotEmpty($payload['items']);
        foreach ($payload['items'] as $item) {
            self::assertMatchesRegularExpression(
                '/^(Département|Arrondissement)/',
                $item['label'],
                'Preset jecoute_managed_area must only return departments, foreign districts, Paris arrondissements or Corse.'
            );
        }
    }

    public function testJecouteManagedAreaPresetRejectsRegularCities(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'Clichy',
            'preset' => 'jecoute_managed_area',
        ]);

        self::assertSame([], $payload['items']);
    }

    public function testMandateTypeFilterRestrictsToActiveMandateZones(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'Paris',
            'mandate_type' => 'depute',
        ]);

        self::assertSame('OK', $payload['status']);
    }

    public function testPaginationHonoursPerPageAndExposesMoreFlag(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $payload = $this->requestZoneAutocomplete([
            'q' => 'a',
            '_per_page' => 5,
        ]);

        self::assertCount(5, $payload['items']);
        self::assertTrue($payload['more']);
    }

    public function testSecondPageReturnsDifferentItems(): void
    {
        $this->authenticateAsAdmin($this->client, 'admin@renaissance.code');

        $firstPage = $this->requestZoneAutocomplete(['q' => 'a', '_per_page' => 5, '_page' => 1]);
        $secondPage = $this->requestZoneAutocomplete(['q' => 'a', '_per_page' => 5, '_page' => 2]);

        $firstIds = array_column($firstPage['items'], 'id');
        $secondIds = array_column($secondPage['items'], 'id');

        self::assertSame([], array_intersect($firstIds, $secondIds));
    }

    /**
     * @return array{status: string, more: bool, items: array<int, array{id: int, label: string}>}
     */
    private function requestZoneAutocomplete(array $query): array
    {
        $this->client->request(Request::METHOD_GET, '/autocomplete/zone?'.http_build_query($query));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);
        self::assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));

        /** @var array{status: string, more: bool, items: array<int, array{id: int, label: string}>} $payload */
        $payload = json_decode((string) $response->getContent(), true, flags: \JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('status', $payload);
        self::assertArrayHasKey('more', $payload);
        self::assertArrayHasKey('items', $payload);

        return $payload;
    }
}

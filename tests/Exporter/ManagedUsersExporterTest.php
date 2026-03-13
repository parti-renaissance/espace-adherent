<?php

declare(strict_types=1);

namespace Tests\App\Exporter;

use App\Adherent\Tag\TagTranslator;
use App\Entity\Geo\Zone;
use App\Exporter\ManagedUsersExporter;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Projection\ManagedUserRepository;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use App\Subscription\SubscriptionTypeEnum;
use App\ValueObject\Genders;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\Exporter\ExporterInterface;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManagedUsersExporterTest extends TestCase
{
    private ?MockObject $sonataExporter = null;
    private ?MockObject $repository = null;
    private ?MockObject $tagTranslator = null;
    private ?MockObject $translator = null;
    private ?MockObject $scopeGeneratorResolver = null;
    private ?ManagedUsersExporter $exporter = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sonataExporter = $this->createMock(ExporterInterface::class);
        $this->repository = $this->createMock(ManagedUserRepository::class);
        $this->tagTranslator = $this->createMock(TagTranslator::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->scopeGeneratorResolver = $this->createMock(ScopeGeneratorResolver::class);

        $this->exporter = new ManagedUsersExporter(
            $this->sonataExporter,
            $this->repository,
            $this->tagTranslator,
            $this->translator,
            $this->scopeGeneratorResolver
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->sonataExporter = null;
        $this->repository = null;
        $this->tagTranslator = null;
        $this->translator = null;
        $this->scopeGeneratorResolver = null;
        $this->exporter = null;
    }

    public function testGetResponseWithStandardExport(): void
    {
        $filter = $this->createFilter();

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('612345678');

        $rowData = [
            'publicId' => 'ABC1234',
            'gender' => Genders::MALE,
            'firstName' => 'Jean',
            'lastName' => 'Dupont',
            'birthdate' => new \DateTime('1980-05-15'),
            'phone' => $phone,
            'committee' => 'Comite Paris',
            'roles' => [
                ['code' => 'president', 'label' => 'Président'],
                ['code' => 'animator', 'label' => 'Animateur', 'is_delegated' => true],
            ],
            'tags' => ['adherent:plus_a_jour:annee_2024', 'elu:cotisation_ok', 'national_event:present:event1'],
            'declaredMandates' => ['maire', 'conseiller'],
            'mandates' => ['Conseiller municipal|Paris (75001)'],
            'createdAt' => new \DateTime('2023-01-15 10:00:00'),
            'firstMembershipDonation' => new \DateTime('2023-02-01 14:30:00'),
            'lastMembershipDonation' => new \DateTime('2024-01-15 09:00:00'),
            'lastLoggedAt' => new \DateTime('2024-03-10 16:45:00'),
            'address' => '10 rue de la Paix',
            'postalCode' => '75001',
            'city' => 'Paris',
            'country' => 'FR',
            'mailchimpStatus' => ContactStatusEnum::SUBSCRIBED,
            'subscriptionTypes' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->with($filter)
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        $this->translator
            ->expects($this->exactly(2))
            ->method('trans')
            ->willReturnMap([
                ['role.president', ['gender' => Genders::MALE], null, null, 'President'],
                ['role.animator', ['gender' => Genders::MALE], null, null, 'Animateur'],
            ])
        ;

        $this->tagTranslator
            ->expects($this->exactly(3))
            ->method('trans')
            ->willReturnCallback(function (string $tag) {
                return match ($tag) {
                    'adherent:plus_a_jour:annee_2024' => 'Adherent a jour 2024',
                    'elu:cotisation_ok' => 'Elu cotisation OK',
                    'national_event:present:event1' => 'Present evenement 1',
                    default => $tag,
                };
            })
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $response = $this->exporter->getResponse('csv', $filter);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertInstanceOf(IteratorCallbackSourceIterator::class, $capturedSource);

        // Execute the callback to verify the row transformation
        $rows = iterator_to_array($capturedSource);
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertSame('ABC1234', $row['PID']);
        $this->assertSame('M', $row['Civilité']);
        $this->assertSame('Jean', $row['Prénom']);
        $this->assertSame('Dupont', $row['Nom']);
        $this->assertSame('15/05/1980', $row['Date de naissance']);
        $this->assertSame('+33 6 12 34 56 78', $row['Téléphone']);
        $this->assertSame('Comite Paris', $row['Comité']);
        $this->assertSame('President, Animateur', $row['Rôles']);
        $this->assertSame('Adherent a jour 2024', $row['Labels Adhérent']);
        $this->assertSame('Elu cotisation OK', $row['Labels Élu']);
        $this->assertSame('maire, conseiller', $row['Déclaration de mandats']);
        $this->assertSame('Conseiller municipal|Paris (75001)', $row['Mandats']);
        $this->assertSame('Present evenement 1', $row['Labels Divers']);
        $this->assertSame('10 rue de la Paix', $row['Adresse postale']);
        $this->assertSame('75001', $row['Code postal']);
        $this->assertSame('Paris', $row['Ville']);
        $this->assertSame('France', $row['Pays']);
        $this->assertTrue($row['Abonné email']);
        $this->assertTrue($row['Abonné SMS']);

        // Count columns for standard export (excludes Circonscription and Code INSEE which require zones)
        $this->assertCount(23, $row);
    }

    public function testGetResponseWithVoxExport(): void
    {
        $filter = $this->createFilter();

        // VOX export uses pre-computed JSON columns from Go worker
        $rowData = [
            'adherentUuid' => 'a9fc8d48-6f57-4d89-ae73-50b3f9b586f4',
            'publicId' => 'XYZ5678',
            'civility' => 'Madame',
            'gender' => Genders::FEMALE,
            'firstName' => 'Marie',
            'lastName' => 'Martin',
            'age' => 34,
            'birthdate' => new \DateTime('1990-08-22'),
            'createdAt' => new \DateTime('2023-01-15 10:30:00'),
            'firstMembershipDonation' => new \DateTime('2023-02-01'),
            'lastLoggedAt' => new \DateTime('2024-03-10 16:45:00'),
            // JSON columns (Go worker format with lowercase labels - will be translated)
            'adherentTags' => [
                ['code' => 'adherent:plus_a_jour:annee_2024', 'label' => 'adherent plus a jour annee 2024'],
            ],
            'staticTags' => [
                ['code' => 'national_event:present:event1', 'label' => 'national event present event1'],
            ],
            'electTags' => [
                ['code' => 'elu:cotisation_ok', 'label' => 'elu cotisation ok'],
            ],
            'roles' => [
                ['code' => 'animator', 'label' => 'animator', 'is_delegated' => false, 'function' => null],
            ],
            'subscriptions' => [
                'email' => ['available' => true, 'subscribed' => true],
                'sms' => ['available' => true, 'subscribed' => false],
                'mobile' => ['available' => true, 'subscribed' => true],
                'web' => ['available' => true, 'subscribed' => true],
            ],
            // Sensitive fields that should NOT appear in VOX export
            'phone' => null,
            'postalCode' => '69001',
            'city' => 'Lyon',
            'country' => 'FR',
            'address' => '10 rue de Lyon',
            'committee' => 'Comite Lyon',
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->with($filter)
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        // VOX export uses TagTranslator to translate tag codes
        $this->tagTranslator
            ->method('trans')
            ->willReturnCallback(function (string $tag) {
                return match ($tag) {
                    'adherent:plus_a_jour:annee_2024' => 'Adhérent 2024',
                    'national_event:present:event1' => 'Présent Event1',
                    'elu:cotisation_ok' => 'Cotisation_ok',
                    default => $tag,
                };
            })
        ;

        // VOX export uses Translator to translate role codes
        $this->translator
            ->method('trans')
            ->willReturnCallback(function (string $key) {
                return match ($key) {
                    'role.animator' => 'Animateur',
                    default => $key,
                };
            })
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $response = $this->exporter->getResponse('csv', $filter, true);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertInstanceOf(IteratorCallbackSourceIterator::class, $capturedSource);

        // Execute the callback to verify the row transformation
        $rows = iterator_to_array($capturedSource);
        $this->assertCount(1, $rows);

        $row = $rows[0];

        // VOX export should have 16 columns (aligned with API VOX list)
        $this->assertCount(16, $row);

        // Verify the exact columns present (aligned with API VOX list)
        $expectedColumns = [
            'UUID', 'PID', 'Civilité', 'Prénom', 'Nom', 'Âge', 'Date de naissance',
            'Date de création de compte', 'Date de première cotisation', 'Date de dernière activité',
            'Labels Adhérent', 'Labels Statique', 'Labels Élu', 'Rôles',
            'Abonné email', 'Abonné SMS',
        ];
        $this->assertSame($expectedColumns, array_keys($row));

        // Verify values
        $this->assertSame('a9fc8d48-6f57-4d89-ae73-50b3f9b586f4', $row['UUID']);
        $this->assertSame('XYZ5678', $row['PID']);
        $this->assertSame('Madame', $row['Civilité']); // Uses civility field directly
        $this->assertSame('Marie', $row['Prénom']);
        $this->assertSame('Martin', $row['Nom']);
        $this->assertSame(34, $row['Âge']);
        $this->assertSame('22/08/1990', $row['Date de naissance']);
        $this->assertSame('15/01/2023 10:30', $row['Date de création de compte']);
        $this->assertSame('01/02/2023 00:00', $row['Date de première cotisation']);
        $this->assertSame('10/03/2024 16:45', $row['Date de dernière activité']);
        $this->assertSame('Adhérent 2024', $row['Labels Adhérent']);
        $this->assertSame('Présent Event1', $row['Labels Statique']);
        $this->assertSame('Cotisation_ok', $row['Labels Élu']);
        $this->assertSame('Animateur', $row['Rôles']);
        $this->assertTrue($row['Abonné email']); // From subscriptions JSON
        $this->assertFalse($row['Abonné SMS']); // From subscriptions JSON

        // Verify that sensitive/standard-only columns are NOT present
        $this->assertArrayNotHasKey('Téléphone', $row);
        $this->assertArrayNotHasKey('Adresse postale', $row);
        $this->assertArrayNotHasKey('Code postal', $row);
        $this->assertArrayNotHasKey('Ville', $row);
        $this->assertArrayNotHasKey('Pays', $row);
        $this->assertArrayNotHasKey('Comité', $row);
        $this->assertArrayNotHasKey('Déclaration de mandats', $row);
        $this->assertArrayNotHasKey('Mandats', $row);
        $this->assertArrayNotHasKey('Labels Divers', $row);
    }

    public function testVoxEmailSubscriptionFromJsonColumn(): void
    {
        $filter = $this->createFilter();

        // VOX export uses subscriptions JSON column directly
        $rowData = [
            'publicId' => 'TEST001',
            'gender' => Genders::MALE,
            'firstName' => 'Test',
            'lastName' => 'User',
            'subscriptions' => [
                'email' => ['available' => true, 'subscribed' => true],
                'sms' => ['available' => true, 'subscribed' => false],
            ],
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $this->exporter->getResponse('csv', $filter, true);

        $rows = iterator_to_array($capturedSource);
        $row = $rows[0];

        $this->assertTrue($row['Abonné email']);
        $this->assertFalse($row['Abonné SMS']);
    }

    public function testVoxEmailNotSubscribedFromJsonColumn(): void
    {
        $filter = $this->createFilter();

        // VOX export uses subscriptions JSON column directly
        $rowData = [
            'publicId' => 'TEST002',
            'gender' => Genders::MALE,
            'firstName' => 'Test',
            'lastName' => 'User',
            'subscriptions' => [
                'email' => ['available' => true, 'subscribed' => false],
                'sms' => ['available' => false, 'subscribed' => false],
            ],
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $this->exporter->getResponse('csv', $filter, true);

        $rows = iterator_to_array($capturedSource);
        $row = $rows[0];

        $this->assertFalse($row['Abonné email']);
        $this->assertFalse($row['Abonné SMS']);
    }

    public function testVoxSubscriptionsNullFromJsonColumn(): void
    {
        $filter = $this->createFilter();

        // VOX export with null subscriptions
        $rowData = [
            'publicId' => 'TEST003',
            'gender' => Genders::MALE,
            'firstName' => 'Test',
            'lastName' => 'User',
            'subscriptions' => null,
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $this->exporter->getResponse('csv', $filter, true);

        $rows = iterator_to_array($capturedSource);
        $row = $rows[0];

        $this->assertFalse($row['Abonné email']);
        $this->assertFalse($row['Abonné SMS']);
    }

    public function testStandardExportEmailSubscriptionWithScopeSpecificType(): void
    {
        $filter = $this->createFilter();

        // Standard export still uses mailchimpStatus + subscriptionTypes
        $rowData = [
            'publicId' => 'TEST004',
            'gender' => Genders::MALE,
            'firstName' => 'Test',
            'lastName' => 'User',
            'mailchimpStatus' => ContactStatusEnum::SUBSCRIBED,
            'subscriptionTypes' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'phone' => $this->createPhone(),
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $scope = $this->createMock(Scope::class);
        $scope
            ->expects($this->once())
            ->method('getCode')
            ->willReturn(ScopeEnum::REGIONAL_COORDINATOR)
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn($scope)
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        // Standard export (not VOX)
        $this->exporter->getResponse('csv', $filter, false);

        $rows = iterator_to_array($capturedSource);
        $row = $rows[0];

        $this->assertTrue($row['Abonné email']);
        $this->assertTrue($row['Abonné SMS']);
    }

    #[DataProvider('provideCivilityLabels')]
    public function testCivilityLabels(?string $gender, string $expected): void
    {
        $filter = $this->createFilter();

        $rowData = [
            'publicId' => 'TEST_CIVILITY',
            'gender' => $gender,
            'firstName' => 'Test',
            'lastName' => 'User',
            'mailchimpStatus' => ContactStatusEnum::SUBSCRIBED,
            'subscriptionTypes' => [],
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $this->exporter->getResponse('csv', $filter, true);

        $rows = iterator_to_array($capturedSource);
        $this->assertSame($expected, $rows[0]['Civilité']);
    }

    public static function provideCivilityLabels(): iterable
    {
        yield 'male' => [Genders::MALE, 'M'];
        yield 'female' => [Genders::FEMALE, 'Mme'];
        yield 'other' => [Genders::OTHER, ''];
        yield 'null' => [null, ''];
    }

    public function testHandlesNullValues(): void
    {
        $filter = $this->createFilter();

        $rowData = [
            'publicId' => null,
            'gender' => null,
            'firstName' => null,
            'lastName' => null,
            'birthdate' => null,
            'phone' => null,
            'committee' => null,
            'roles' => null,
            'tags' => null,
            'declaredMandates' => null,
            'mandates' => null,
            'createdAt' => null,
            'firstMembershipDonation' => null,
            'lastMembershipDonation' => null,
            'lastLoggedAt' => null,
            'address' => null,
            'postalCode' => null,
            'city' => null,
            'country' => null,
            'mailchimpStatus' => null,
            'subscriptionTypes' => null,
        ];

        $this->repository
            ->expects($this->once())
            ->method('iterateForExport')
            ->willReturn($this->createGenerator([$rowData]))
        ;

        $this->scopeGeneratorResolver
            ->expects($this->once())
            ->method('generate')
            ->willReturn(null)
        ;

        $capturedSource = null;
        $this->sonataExporter
            ->expects($this->once())
            ->method('getResponse')
            ->willReturnCallback(function (string $format, string $filename, \Iterator $source) use (&$capturedSource) {
                $capturedSource = $source;

                return new StreamedResponse();
            })
        ;

        $response = $this->exporter->getResponse('csv', $filter);

        $this->assertInstanceOf(StreamedResponse::class, $response);

        $rows = iterator_to_array($capturedSource);
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertNull($row['PID']);
        $this->assertSame('', $row['Civilité']);
        $this->assertNull($row['Prénom']);
        $this->assertNull($row['Nom']);
        $this->assertNull($row['Date de naissance']);
        $this->assertSame('', $row['Téléphone']); // PhoneNumberUtils::format returns '' for null
        $this->assertNull($row['Comité']);
        $this->assertSame('', $row['Rôles']);
        $this->assertSame('', $row['Labels Adhérent']);
        $this->assertSame('', $row['Labels Élu']);
        $this->assertSame('', $row['Déclaration de mandats']); // implode on empty array returns ''
        $this->assertSame('', $row['Mandats']); // implode on empty array returns ''
        $this->assertSame('', $row['Labels Divers']);
        $this->assertNull($row['Date de création de compte']);
        $this->assertNull($row['Date de première cotisation']);
        $this->assertNull($row['Date de dernière cotisation']);
        $this->assertNull($row['Date de dernière connexion']);
        $this->assertNull($row['Adresse postale']);
        $this->assertNull($row['Code postal']);
        $this->assertNull($row['Ville']);
        $this->assertNull($row['Pays']);
        $this->assertFalse($row['Abonné email']);
        $this->assertFalse($row['Abonné SMS']);
    }

    private function createFilter(): ManagedUsersFilter
    {
        $zone = $this->createMock(Zone::class);
        $zone->method('getId')->willReturn(1);

        return new ManagedUsersFilter(managedZones: [$zone]);
    }

    /**
     * @param array<array<string, mixed>> $items
     */
    private function createGenerator(array $items): \Generator
    {
        yield from $items;
    }

    private function createPhone(): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('612345678');

        return $phone;
    }
}

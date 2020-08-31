<?php

namespace Tests\App\Command\Geo;

use App\Command\Geo\UpdateFranceCommand;
use App\Entity\Geo\City;
use App\Entity\Geo\Country;
use App\Entity\Geo\Department;
use App\Entity\Geo\Region;
use Doctrine\ORM\EntityManagerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @group command
 * @group geo
 */
final class UpdateFranceCommandTest extends WebTestCase
{
    private const PAYLOAD = [
        [
            'nom' => 'City A-A-A',
            'code' => 'A-A-A',
            'population' => 1001,
            'departement' => ['code' => 'A-A', 'nom' => 'Department A-A'],
            'region' => ['code' => 'A', 'nom' => 'Region A'],
        ],
        [
            'nom' => 'City A-A-B',
            'code' => 'A-A-B',
            'population' => 1001,
            'departement' => ['code' => 'A-A', 'nom' => 'Department A-A'],
            'region' => ['code' => 'A', 'nom' => 'Region A'],
        ],
        [
            'nom' => 'City B-A-A',
            'code' => 'B-A-A',
            'population' => 1001,
            'departement' => ['code' => 'B-A', 'nom' => 'Department B-A'],
            'region' => ['code' => 'B', 'nom' => 'Region B'],
        ],
    ];

    public function testDryRunDoNotWrite(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        /* @var UpdateFranceCommand $command */
        $command = $application->find('app:geo:update-france');

        // HTTP Client (Mock)
        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('apiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse(json_encode(self::PAYLOAD, \JSON_THROW_ON_ERROR)),
        ], 'http://null'));

        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertContains('Nothing was persisted in database', $output);

        // @todo remove it once it's present in fixtures
        $this->assertFalse($this->exists(Country::class, 'FR'));

        $this->assertFalse($this->exists(Region::class, 'A'));
        $this->assertFalse($this->exists(Region::class, 'B'));

        $this->assertFalse($this->exists(Department::class, 'A-A'));
        $this->assertFalse($this->exists(Department::class, 'B-A'));

        $this->assertFalse($this->exists(City::class, 'A-A-A'));
        $this->assertFalse($this->exists(City::class, 'A-A-B'));
        $this->assertFalse($this->exists(City::class, 'B-A-A'));
    }

    public function testPersistingCommand(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        /* @var UpdateFranceCommand $command */
        $command = $application->find('app:geo:update-france');

        // HTTP Client (Mock)
        $reflection = new \ReflectionClass(\get_class($command));
        $property = $reflection->getProperty('apiClient');
        $property->setAccessible(true);
        $property->setValue($command, new MockHttpClient([
            new MockResponse(json_encode(self::PAYLOAD, \JSON_THROW_ON_ERROR)),
        ], 'http://null'));

        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);

        $this->assertSame(0, $exitCode);

        $output = $commandTester->getDisplay();
        $this->assertNotContains('Nothing was persisted in database', $output);

        $this->assertTrue($this->exists(Country::class, 'FR'));

        $this->assertTrue($this->exists(Region::class, 'A'));
        $this->assertTrue($this->exists(Region::class, 'B'));

        $this->assertTrue($this->exists(Department::class, 'A-A'));
        $this->assertTrue($this->exists(Department::class, 'B-A'));

        $this->assertTrue($this->exists(City::class, 'A-A-A'));
        $this->assertTrue($this->exists(City::class, 'A-A-B'));
        $this->assertTrue($this->exists(City::class, 'B-A-A'));
    }

    private function exists(string $class, string $code): bool
    {
        /* @var EntityManagerInterface $repository */
        $repository = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        return (bool) $repository->getRepository($class)->findOneBy([
            'code' => $code,
        ]);
    }
}

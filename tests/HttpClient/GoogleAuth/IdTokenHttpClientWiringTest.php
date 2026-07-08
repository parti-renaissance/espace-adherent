<?php

declare(strict_types=1);

namespace Tests\App\HttpClient\GoogleAuth;

use App\HttpClient\GoogleAuth\IdTokenHttpClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class IdTokenHttpClientWiringTest extends KernelTestCase
{
    private ?string $originalIndexerUrl = null;
    private ?string $originalRankerUrl = null;

    protected function setUp(): void
    {
        // Getting the scoped clients instantiates them, and ScopingHttpClient rejects an empty base_uri.
        // The test env leaves TIMELINE_*_URL empty (and GetTimelineFeedsIndexerControllerTest resets the ranker one to ''), so
        // set non-empty https origins here; the values only need to be valid URLs.
        $this->originalIndexerUrl = $_SERVER['TIMELINE_INDEXER_URL'] ?? null;
        $this->originalRankerUrl = $_SERVER['TIMELINE_RANKER_URL'] ?? null;
        $_SERVER['TIMELINE_INDEXER_URL'] = $_ENV['TIMELINE_INDEXER_URL'] = 'https://indexer.example.run.app';
        $_SERVER['TIMELINE_RANKER_URL'] = $_ENV['TIMELINE_RANKER_URL'] = 'https://ranker.example.run.app';
    }

    protected function tearDown(): void
    {
        $this->restoreEnv('TIMELINE_INDEXER_URL', $this->originalIndexerUrl);
        $this->restoreEnv('TIMELINE_RANKER_URL', $this->originalRankerUrl);

        parent::tearDown();
    }

    /**
     * The decorator is wired only in config/services_prod.php, so the scoped clients must stay plain
     * outside production: no Google ID token is ever fetched in dev/test/CI (no metadata server / ADC)
     * and the existing timeline tests keep using the raw clients. Production decoration is verified out
     * of band (debug:container --env=prod shows IdTokenHttpClient).
     */
    public function testScopedClientsAreNotDecoratedOutsideProd(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        self::assertNotInstanceOf(IdTokenHttpClient::class, $container->get('timeline_indexer.client'));
        self::assertNotInstanceOf(IdTokenHttpClient::class, $container->get('timeline_ranker.client'));
    }

    private function restoreEnv(string $name, ?string $original): void
    {
        if (null === $original) {
            unset($_SERVER[$name], $_ENV[$name]);

            return;
        }

        $_SERVER[$name] = $_ENV[$name] = $original;
    }
}

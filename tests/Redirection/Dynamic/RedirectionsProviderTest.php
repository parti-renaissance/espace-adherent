<?php

declare(strict_types=1);

namespace Tests\App\Redirection\Dynamic;

use App\Redirection\Dynamic\RedirectionsProvider;
use PHPUnit\Framework\TestCase;

class RedirectionsProviderTest extends TestCase
{
    public function testProdiver()
    {
        $provider = new RedirectionsProvider();

        // Valid value
        $toRoute = $provider->get($provider::TO_ROUTE);
        $this->assertNotEmpty($toRoute);

        // Invalid value
        $foo = $provider->get('foo');
        $this->assertEmpty($foo);
    }
}

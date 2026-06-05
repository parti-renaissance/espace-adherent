<?php

declare(strict_types=1);

namespace Tests\App\Renaissance\App;

use App\AppCodeEnum;
use App\Renaissance\App\UrlGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UrlGeneratorTest extends TestCase
{
    private const USER_VOX_HOST = 'utilisateur.renaissance.code';
    private const VOX_HOST = 'vox.code';

    public function testGetAppCode(): void
    {
        self::assertSame(AppCodeEnum::RENAISSANCE, UrlGenerator::getAppCode());
    }

    public function testGetAppHostReturnsAuthHost(): void
    {
        $generator = new UrlGenerator($this->createStub(UrlGeneratorInterface::class), self::USER_VOX_HOST, self::VOX_HOST);

        self::assertSame(self::USER_VOX_HOST, $generator->getAppHost());
    }

    public function testGetSpaHostReturnsVoxHost(): void
    {
        $generator = new UrlGenerator($this->createStub(UrlGeneratorInterface::class), self::USER_VOX_HOST, self::VOX_HOST);

        self::assertSame(self::VOX_HOST, $generator->getSpaHost());
    }
}

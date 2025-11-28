<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadQrCodeData;
use App\Entity\QrCode;
use App\Repository\QrCodeRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class QrCodeControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    /** @var QrCodeRepository */
    private $qrCodeRepository;

    public function testQrCodeRedirection(): void
    {
        $qrCode = $this->qrCodeRepository->findOneByUuid(LoadQrCodeData::QR_CODE_2_UUID);

        self::assertEquals(10, $qrCode->getCount());
        self::assertEquals('https://pourunecause.fr/creer-une-cause', $qrCode->getRedirectUrl());

        $this->client->request('GET', \sprintf('/qr-code/%s', $qrCode->getUuid()->toString()));

        self::assertClientIsRedirectedTo('https://pourunecause.fr/creer-une-cause', $this->client);

        $this->manager->refresh($qrCode);

        self::assertEquals(11, $qrCode->getCount());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->qrCodeRepository = $this->getRepository(QrCode::class);
    }

    protected function tearDown(): void
    {
        $this->qrCodeRepository = null;

        parent::tearDown();
    }
}

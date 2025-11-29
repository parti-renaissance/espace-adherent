<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Entity\Ohme\Contact;
use App\Repository\Ohme\ContactRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractCommandTestCase;

#[Group('functional')]
class OhmeImportCommandTest extends AbstractCommandTestCase
{
    private ?ContactRepository $contactRepository = null;

    public function testCommandSuccess(): void
    {
        self::assertCount(0, $this->contactRepository->findAll());

        $output = $this->runCommand('app:ohme:import', ['--with-payments' => true]);
        $output = $output->getDisplay();

        self::assertStringContainsString('[OK] 3 contacts handled successfully.', $output);
        self::assertStringContainsString('[OK] 3 payments handled successfully.', $output);
        self::assertCount(3, $this->contactRepository->findAll());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = $this->getRepository(Contact::class);
    }

    protected function tearDown(): void
    {
        $this->contactRepository = null;

        parent::tearDown();
    }
}

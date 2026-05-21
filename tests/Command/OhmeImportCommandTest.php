<?php

declare(strict_types=1);

namespace Tests\App\Command;

use App\Entity\Contribution\Payment;
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

        // Payments are only persisted for contacts linked to an adherent (c_123, c_456).
        $paymentRepository = $this->getRepository(Payment::class);
        self::assertNotNull($paymentRepository->findOneByOhmeIdentifier('p_123'));
        self::assertNotNull($paymentRepository->findOneByOhmeIdentifier('p_456'));
        self::assertNotNull($paymentRepository->findOneByOhmeIdentifier('p_789'));

        $contact = $this->contactRepository->findOneByOhmeIdentifier('c_123');
        self::assertInstanceOf(\DateTimeImmutable::class, $contact->lastPaymentDate);
        self::assertSame('2024-02-26 17:30:30', $contact->lastPaymentDate->format('Y-m-d H:i:s'));
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

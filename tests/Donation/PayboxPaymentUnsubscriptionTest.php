<?php

declare(strict_types=1);

namespace Tests\App\Donation;

use App\Donation\Paybox\PayboxPaymentUnsubscription;
use App\Entity\Donation;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Mailer\MailerService;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\App\AbstractKernelTestCase;

class PayboxPaymentUnsubscriptionTest extends AbstractKernelTestCase
{
    public function testUnsubscribError(): void
    {
        $donation = $this->createStub(Donation::class);

        $payboxPaymentUnsubscribtion = $this->createPayboxPaymentUnsubscriptionError();

        $this->expectException(PayboxPaymentUnsubscriptionException::class);
        $this->expectExceptionMessage('Échec de la résiliation. Aucun abonnement résilié');

        $payboxPaymentUnsubscribtion->unsubscribe($donation);
    }

    public function testUnsubscribSuccess(): void
    {
        /** @var MockObject|Donation $donation */
        $donation = $this->createMock(Donation::class);
        $donation->expects($this->once())->method('stopSubscription');

        $payboxPaymentUnsubscribtion = $this->createPayboxPaymentUnsubscriptionSuccess();

        $payboxPaymentUnsubscribtion->unsubscribe($donation);
    }

    private function createPayboxPaymentUnsubscriptionError(): PayboxPaymentUnsubscription
    {
        $this->createConfiguredStub(Request::class, []);

        return new PayboxPaymentUnsubscription(
            $this->createConfiguredStub(MailerService::class, []),
            $this->createConfiguredStub(Request::class, [
                'cancel' => 'ACQ=NO&ERREUR=9&IDENTIFIANT=2&REFERENCE=refcmd1',
            ]),
        );
    }

    private function createPayboxPaymentUnsubscriptionSuccess(): PayboxPaymentUnsubscription
    {
        $this->createConfiguredStub(Request::class, []);

        return new PayboxPaymentUnsubscription(
            $this->createConfiguredStub(MailerService::class, []),
            $this->createConfiguredStub(Request::class, ['cancel' => 'ACQ=OK&IDENTIFIANT=2&REFERENCE=refcmd1']),
        );
    }
}

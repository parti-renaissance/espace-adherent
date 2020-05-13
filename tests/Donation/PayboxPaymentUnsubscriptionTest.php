<?php

namespace Tests\App\Donation;

use App\Donation\DonationRequestUtils;
use App\Donation\PayboxPaymentUnsubscription;
use App\Entity\Donation;
use App\Exception\PayboxPaymentUnsubscriptionException;
use App\Mailer\MailerService;
use Lexik\Bundle\PayboxBundle\Paybox\System\Cancellation\Request;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class PayboxPaymentUnsubscriptionTest extends WebTestCase
{
    public const DONATION_REQUEST_UUID = 'cfd3c04f-cce0-405d-865f-f5f3a2c1792e';

    public function testUnsubscribError(): void
    {
        $donation = $this->createMock(Donation::class);

        $payboxPaymentUnsubscribtion = $this->createPayboxPaymentUnsubscriptionError();

        $this->expectException(PayboxPaymentUnsubscriptionException::class);
        $this->expectExceptionMessage('Echec de la résiliation. Aucun abonnement résilié');

        $payboxPaymentUnsubscribtion->unsubscribe($donation);
    }

    public function testUnsubscribSuccess(): void
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Donation $donation */
        $donation = $this->createMock(Donation::class);
        $donation->expects($this->once())->method('stopSubscription');

        $payboxPaymentUnsubscribtion = $this->createPayboxPaymentUnsubscriptionSuccess();

        $payboxPaymentUnsubscribtion->unsubscribe($donation);
    }

    private function createPayboxPaymentUnsubscriptionError(): PayboxPaymentUnsubscription
    {
        $this->createConfiguredMock(Request::class, []);

        return new PayboxPaymentUnsubscription(
            $this->createConfiguredMock(MailerService::class, []),
            $this->createConfiguredMock(Request::class, [
                'cancel' => 'ACQ=NO&ERREUR=9&IDENTIFIANT=2&REFERENCE=refcmd1',
            ]),
            $this->getContainer()->get(DonationRequestUtils::class)
        );
    }

    private function createPayboxPaymentUnsubscriptionSuccess(): PayboxPaymentUnsubscription
    {
        $this->createConfiguredMock(Request::class, []);

        return new PayboxPaymentUnsubscription(
            $this->createConfiguredMock(MailerService::class, []),
            $this->createConfiguredMock(Request::class, [
                'cancel' => 'ACQ=OK&IDENTIFIANT=2&REFERENCE=refcmd1',
            ]),
            $this->getContainer()->get(DonationRequestUtils::class)
        );
    }
}

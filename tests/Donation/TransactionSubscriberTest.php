<?php

declare(strict_types=1);

namespace Tests\App\Donation;

use App\Repository\AdherentRepository;
use App\Repository\DonationRepository;
use App\Repository\TransactionRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\TestHelperTrait;

#[Group('functional')]
class TransactionSubscriberTest extends AbstractEnMarcheWebTestCase
{
    use TestHelperTrait;

    private ?DonationRepository $donationRepository;
    private ?AdherentRepository $adherentRepository;
    private ?TransactionRepository $transactionRepository;

    public static function payloadProvider(): iterable
    {
        yield 'transaction1' => ['424241'];
        yield 'transaction2' => ['424242'];
        yield 'transaction3' => ['424243'];
    }

    #[DataProvider('payloadProvider')]
    public function testOnPayboxIpnResponse(string $transactionId): void
    {
        static::assertNull($this->transactionRepository->findByPayboxTransactionId($transactionId));
        $params = $this->createPayload($transactionId);

        $this->makeRenaissanceClient();
        $this->client->request(Request::METHOD_POST, '/paybox/payment-ipn/1528114726', $params);

        static::assertSame('OK', $this->client->getResponse()->getContent());

        static::assertNotNull($this->transactionRepository->findByPayboxTransactionId($transactionId));
    }

    #[Depends('testOnPayboxIpnResponse')]
    public function testAllTransactionCreated(): void
    {
        $transactions = $this->transactionRepository->findAllTransactionByAdherentIdOrEmail($this->adherentRepository->findOneByEmail('jacques.picard@en-marche.fr'));

        // b/c there are initial transactions when donationFixtures are loaded then 5+4 = 9
        static::assertCount(9, $transactions);
    }

    private function createSignature(array $params): string
    {
        $queryParams = http_build_query($params);
        $privateKey = openssl_pkey_get_private($this->getParameter('ssl_private_key'));
        openssl_sign($queryParams, $signature, $privateKey, 'sha1WithRSAEncryption');

        return urlencode(base64_encode($signature));
    }

    private function createPayload(string $transactionId): array
    {
        $donation = $this->donationRepository->findAllSubscribedDonationByEmail('jacques.picard@en-marche.fr')[0];

        $params = [
            'id' => $donation->getUuid()->toString().'_test',
            'authorization' => 'XXXXXX',
            'result' => '00000',
            'transaction' => $transactionId,
            'amount' => '50000',
            'date' => '01062018',
            'time' => '14:52:22',
            'card_type' => 'MasterCard',
            'card_end' => '1812',
            'card_print' => '5B434C778490889697170E225029F56AFF19CA47',
            'subscription' => '1234',
        ];

        $params['Sign'] = $this->createSignature($params);

        return $params;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->donationRepository = $this->getDonationRepository();
        $this->adherentRepository = $this->getAdherentRepository();
        $this->transactionRepository = $this->getTransactionRepository();
    }

    protected function tearDown(): void
    {
        $this->donationRepository = null;
        $this->adherentRepository = null;
        $this->transactionRepository = null;

        parent::tearDown();
    }
}

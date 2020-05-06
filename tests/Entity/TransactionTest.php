<?php

namespace Tests\App\Entity;

use App\Entity\Donation;
use App\Entity\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public function constructProvider(): iterable
    {
        yield 'full_success' => [
            [
                'result' => Transaction::PAYBOX_SUCCESS,
                'transaction' => '42',
                'date' => '02022018',
                'time' => '15:16:17',
                'authorization' => 'XXXXXX',
                'subscription' => '21',
            ],
            [
                'isSuccessful' => true,
                'getPayboxResultCode' => Transaction::PAYBOX_SUCCESS,
                'getPayboxAuthorizationCode' => 'XXXXXX',
                'getPayboxDateTime' => \DateTimeImmutable::createFromFormat('Y/m/d H:i:s', '2018/02/02 15:16:17'),
                'getPayboxTransactionId' => '42',
                'getPayboxSubscriptionId' => '21',
            ],
        ];
        yield 'with_error' => [
            [
                'result' => Transaction::PAYBOX_INTERNAL_ERROR,
                'transaction' => '0',
                'date' => '02022018',
                'time' => null,
                'authorization' => null,
                'subscription' => '',
            ],
            [
                'isSuccessful' => false,
                'getPayboxResultCode' => Transaction::PAYBOX_INTERNAL_ERROR,
                'getPayboxAuthorizationCode' => null,
                'getPayboxDateTime' => null,
                'getPayboxTransactionId' => null,
                'getPayboxSubscriptionId' => null,
            ],
        ];
        yield 'missing_fields' => [
            [
                'result' => Transaction::PAYBOX_INTERNAL_ERROR,
                'transaction' => '0',
                'subscription' => '0',
                'authorization' => null,
            ],
            [
                'isSuccessful' => false,
                'getPayboxResultCode' => Transaction::PAYBOX_INTERNAL_ERROR,
                'getPayboxAuthorizationCode' => null,
                'getPayboxDateTime' => null,
                'getPayboxTransactionId' => null,
                'getPayboxSubscriptionId' => null,
            ],
        ];
    }

    /**
     * @dataProvider constructProvider
     */
    public function testConstruct(array $payload, array $expectations): void
    {
        $transaction = new Transaction($this->createMock(Donation::class), $payload);

        foreach ($expectations as $key => $expectation) {
            if ($expectation instanceof \DateTimeInterface) {
                self::assertSame($expectation->format('YmdHis'), $transaction->$key()->format('YmdHis'));
            } else {
                self::assertSame($expectation, $transaction->$key());
            }
        }
    }
}

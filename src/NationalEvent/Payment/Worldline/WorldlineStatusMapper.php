<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

use App\NationalEvent\PaymentStatusEnum;

/**
 * Translates a Worldline payment payload into a project status.
 *
 * The numeric statusCode is the primary key because it is stable across API versions, the status string is only a
 * fallback. Both tables are pinned against the Worldline test cases during the preprod validation.
 */
class WorldlineStatusMapper
{
    private const STATUS_CODES = [
        2 => PaymentStatusEnum::ERROR,
        5 => PaymentStatusEnum::PENDING,
        8 => PaymentStatusEnum::REFUNDED,
        9 => PaymentStatusEnum::CONFIRMED,
        85 => PaymentStatusEnum::REFUNDED,
        91 => PaymentStatusEnum::PENDING,
        92 => PaymentStatusEnum::PENDING,
        99 => PaymentStatusEnum::PENDING,
    ];

    private const STATUSES = [
        'CAPTURED' => PaymentStatusEnum::CONFIRMED,
        'PAID' => PaymentStatusEnum::CONFIRMED,
        'REFUNDED' => PaymentStatusEnum::REFUNDED,
        'AUTHORIZATION_REQUESTED' => PaymentStatusEnum::PENDING,
        'PENDING_CAPTURE' => PaymentStatusEnum::PENDING,
        'CAPTURE_REQUESTED' => PaymentStatusEnum::PENDING,
        'REDIRECTED' => PaymentStatusEnum::PENDING,
        'PENDING_PAYMENT' => PaymentStatusEnum::PENDING,
        'REFUND_REQUESTED' => PaymentStatusEnum::PENDING,
        'REJECTED' => PaymentStatusEnum::ERROR,
        'REJECTED_CAPTURE' => PaymentStatusEnum::ERROR,
        'CANCELLED' => PaymentStatusEnum::ERROR,
    ];

    public function map(array $payment): PaymentStatusEnum
    {
        $statusCode = $payment['statusOutput']['statusCode'] ?? null;

        if (is_numeric($statusCode) && isset(self::STATUS_CODES[(int) $statusCode])) {
            return self::STATUS_CODES[(int) $statusCode];
        }

        $status = $payment['status'] ?? null;

        if (\is_string($status) && isset(self::STATUSES[strtoupper($status)])) {
            return self::STATUSES[strtoupper($status)];
        }

        return PaymentStatusEnum::UNKNOWN;
    }
}

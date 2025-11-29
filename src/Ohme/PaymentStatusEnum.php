<?php

declare(strict_types=1);

namespace App\Ohme;

class PaymentStatusEnum
{
    public const CONFIRMED = 'confirmed';
    public const PAID_OUT = 'paid_out';
    public const PENDING_CUSTOMER_APPROVAL = 'pending_customer_approval';
    public const PENDING_SUBMISSION = 'pending_submission';
    public const SUBMITTED = 'submitted';
    public const CANCELLED = 'cancelled';
    public const CUSTOMER_APPROVAL_DENIED = 'customer_approval_denied';
    public const FAILED = 'failed';
    public const CHARGED_BACK = 'charged_back';
    public const CHEQUE_CASHED = 'cheque_cashed';
    public const CHEQUE_RECEIVED = 'cheque_received';
    public const CHEQUE_DEPOSITED = 'cheque_deposited';
    public const CHEQUE_REJECTED = 'cheque_rejected';

    public const LABELS = [
        self::CONFIRMED => 'Paiement validé',
        self::PAID_OUT => 'Reversé',
        self::PENDING_CUSTOMER_APPROVAL => 'En attente d\'autorisation',
        self::PENDING_SUBMISSION => 'En attente d\'envoi',
        self::SUBMITTED => 'En attente de traitement',
        self::CANCELLED => 'Annulé',
        self::CUSTOMER_APPROVAL_DENIED => 'Autorisation refusée',
        self::FAILED => 'Échoué',
        self::CHARGED_BACK => 'Remboursé',
        self::CHEQUE_CASHED => 'Chèque encaissé',
        self::CHEQUE_RECEIVED => 'Chèque reçu',
        self::CHEQUE_DEPOSITED => 'Chèque déposé',
        self::CHEQUE_REJECTED => 'Chèque refusé',
    ];

    public const CONFIRMED_PAYMENT_STATUSES = [
        self::CONFIRMED,
        self::PAID_OUT,
        self::CHEQUE_CASHED,
    ];
}

<?php

namespace App\Ohme;

use App\Entity\Contribution\Payment;
use App\Entity\Ohme\Contact;
use App\Repository\Contribution\PaymentRepository;
use App\Repository\Ohme\ContactRepository;

class PaymentImporter
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ContactRepository $contactRepository,
        private readonly PaymentRepository $paymentRepository,
    ) {
    }

    public function getPaymentsCount(array $options = [], ?Contact $contact = null): int
    {
        if ($contact) {
            $options['contact_id'] = $contact->ohmeIdentifier;
        }

        $payments = $this->client->getPayments(1, 0, $options);

        return $payments['count'] ?? 0;
    }

    public function importPayments(int $limit = 100, int $offset = 0, array $options = [], ?Contact $contact = null): void
    {
        if ($contact) {
            $options['contact_id'] = $contact->ohmeIdentifier;
        }

        $payments = $this->client->getPayments($limit, $offset, $options);

        if (empty($payments['data']) || !is_iterable($payments['data'])) {
            return;
        }

        foreach ($payments['data'] as $paymentData) {
            if (empty($paymentData['contact_id'])) {
                continue;
            }

            $currentContact = $contact;

            if (!$currentContact) {
                $currentContact = $this->findContact((string) $paymentData['contact_id']);
            }

            // Do not retrieve payments that can't be associated to an adherent
            if (!$currentContact || !$currentContact->adherent) {
                continue;
            }

            if (empty($paymentData['id'])) {
                continue;
            }

            $identifier = (string) $paymentData['id'];

            $payment = $this->findPayment($identifier) ?? $this->createPayment($identifier);
            $payment->adherent = $currentContact->adherent;

            $this->updatePayment($payment, $paymentData);
        }
    }

    private function findContact(string $identifier): ?Contact
    {
        return $this->contactRepository->findOneByOhmeIdentifier($identifier);
    }

    private function findPayment(string $identifier): ?Payment
    {
        return $this->paymentRepository->findOneByOhmeIdentifier($identifier);
    }

    private function createPayment(string $identifier): Payment
    {
        $payment = new Payment();
        $payment->ohmeId = $identifier;

        return $payment;
    }

    private function updatePayment(Payment $payment, array $data): void
    {
        if (!empty($data['date'])) {
            $payment->date = new \DateTimeImmutable($data['date']);
        }

        if (!empty($data['payment_method_name'])) {
            $payment->method = $data['payment_method_name'];
        }

        if (!empty($data['payment_status'])) {
            $payment->status = $data['payment_status'];
        }

        if (!empty($data['amount'])) {
            $payment->amount = $data['amount'];
        }

        $this->paymentRepository->save($payment);
    }
}

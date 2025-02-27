<?php

namespace App\Ohme;

use App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand;
use App\Entity\Contribution\Payment;
use App\Repository\Contribution\PaymentRepository;
use App\Repository\Ohme\ContactRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

class PaymentImporter
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ContactRepository $contactRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function getPaymentsCount(): int
    {
        $payments = $this->client->getPayments();

        return $payments['count'] ?? 0;
    }

    public function importPayments(int $limit = 100, int $offset = 0, array $options = []): int
    {
        $payments = $this->client->getPayments($limit, $offset, $options);

        if (empty($payments['data']) || !is_iterable($payments['data'])) {
            return 0;
        }

        $total = 0;

        $adherentsUuidToRefresh = [];
        foreach ($payments['data'] as $paymentData) {
            ++$total;

            if (empty($paymentData['contact_id'])) {
                continue;
            }

            $contact = $this->contactRepository->findOneByOhmeIdentifier($paymentData['contact_id']);

            // Do not retrieve payments that can't be associated to an adherent
            if (!$contact || !$contact->adherent) {
                continue;
            }

            if (empty($paymentData['id'])) {
                continue;
            }

            $identifier = (string) $paymentData['id'];

            $payment = $this->findPayment($identifier);

            if (!$payment) {
                $payment = $this->createPayment($identifier);

                $contact->incrementPaymentCount();

                $this->contactRepository->save($contact);
            }

            $payment->adherent = $contact->adherent;

            $this->updatePayment($payment, $paymentData);

            if (
                !$contact->lastPaymentDate
                || $contact->lastPaymentDate < $payment->date
            ) {
                $contact->lastPaymentDate = $payment->date;

                $this->contactRepository->save($contact);
            }

            $adherentUuid = $contact->adherent->getUuid()->toString();
            if (!\in_array($adherentUuid, $adherentsUuidToRefresh)) {
                $adherentsUuidToRefresh[] = $adherentUuid;
            }
        }

        foreach ($adherentsUuidToRefresh as $adherentUuid) {
            $this->bus->dispatch(new AsyncRefreshAdherentTagCommand(Uuid::fromString($adherentUuid)));
        }

        return $total;
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

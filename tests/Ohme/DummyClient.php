<?php

declare(strict_types=1);

namespace Tests\App\Ohme;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Ohme\ClientInterface;
use App\Ohme\PaymentStatusEnum;

class DummyClient implements ClientInterface
{
    public function updateContact(string $contactId, array $data): array
    {
        return [
            'status' => 200,
            'data' => array_merge(['id' => $contactId], $data),
        ];
    }

    public function getContacts(int $limit = 100, int $offset = 0, array $options = []): array
    {
        return [
            'status' => 200,
            'count' => 3,
            'data' => [
                $this->createContact('c_123', 'John', 'Doe', 'johndoe@example.dev', LoadAdherentData::ADHERENT_1_UUID),
                $this->createContact('c_456', 'Jane', 'Doe', 'janedoe@example.dev', LoadAdherentData::ADHERENT_2_UUID),
                $this->createContact('c_789', 'Jack', 'Sparrow', 'jacksparrow@example.dev'),
            ],
        ];
    }

    public function getPayments(int $limit = 100, int $offset = 0, array $options = []): array
    {
        return [
            'status' => 200,
            'count' => 3,
            'data' => [
                $this->createPayment('c_123', 'p_123', '2024-02-26 17:30:30'),
                $this->createPayment('c_123', 'p_456', '2024-01-26 17:30:30'),
                $this->createPayment('c_456', 'p_789', '2024-01-02 09:30:30'),
            ],
        ];
    }

    private function createContact(
        string $id,
        string $firstname,
        string $lastname,
        string $email,
        ?string $uuidAdherent = null,
    ): array {
        return [
            'id' => $id,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'birthdate' => '1990-05-15',
            'created_at' => '2024-01-15 10:00:00',
            'updated_at' => '2024-02-20 14:30:00',
            'uuid_adherent' => $uuidAdherent,
        ];
    }

    private function createPayment(string $contactId, string $id, string $date): array
    {
        return [
            'contact_id' => $contactId,
            'id' => $id,
            'date' => $date,
            'payment_method_name' => 'IBAN',
            'payment_status' => PaymentStatusEnum::PAID_OUT,
            'amount' => 5,
        ];
    }
}

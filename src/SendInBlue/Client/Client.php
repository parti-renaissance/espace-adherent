<?php

namespace App\SendInBlue\Client;

use SendinBlue\Client\Api\ContactsApi;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Model\CreateContact;

class Client implements ClientInterface
{
    private ContactsApi $contactsApi;

    public function __construct(string $sendInBlueApiKey)
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $sendInBlueApiKey);

        $this->contactsApi = new ContactsApi(null, $config);
    }

    public function synchronize(string $email, int $listId, array $attributes): void
    {
        $createContact = (new CreateContact())
            ->setUpdateEnabled(true)
            ->setEmail($email)
            ->setListIds([$listId])
            ->setAttributes($attributes)
        ;

        $this->contactsApi->createContact($createContact);
    }

    public function delete(string $email): void
    {
        $this->contactsApi->deleteContact($email);
    }
}

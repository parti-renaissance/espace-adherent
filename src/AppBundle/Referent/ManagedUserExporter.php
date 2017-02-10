<?php

namespace AppBundle\Referent;

class ManagedUserExporter
{
    /**
     * @param ManagedUser[] $managedUsers
     *
     * @return string
     */
    public function exportAsJson(array $managedUsers): string
    {
        $data = [];

        foreach ($managedUsers as $user) {
            $data[] = [
                'type' => $user->getType(),
                'id' => $user->getId(),
                'postalCode' => $user->getPostalCode(),
                'email' => ($user->getEmail() && $user->isEmailVisible()) ?: '',
                'name' => $user->getPartialName() ?: '',
                'age' => $user->getAge() ?: '',
                'city' => $user->getCity() ?: '',
                'country' => $user->getCountry() ?: '',
                'emailsSubscription' => $user->hasReferentsEmailsSubscription() ? 'Oui' : 'Non',
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}

<?php

namespace AppBundle\Referent;

use Doctrine\Common\Collections\ArrayCollection;

class ManagedUserCollection extends ArrayCollection
{
    public function exportJson(): string
    {
        $data = [];

        /** @var ManagedUser $user */
        foreach ($this->toArray() as $user) {
            $data[] = [
                'type' => $user->getType(),
                'id' => $user->getId(),
                'postalCode' => $user->getPostalCode(),
                'email' => ($user->getEmail() && $user->isEmailVisible()) ?: '',
                'name' => $user->getName() ?: '',
                'age' => $user->getAge() ?: '',
                'city' => $user->getCity() ?: '',
                'country' => $user->getCountry() ?: '',
                'emailsSubscription' => $user->hasReferentsEmailsSubscription() ? 'Oui' : 'Non',
            ];
        }

        return \GuzzleHttp\json_encode($data);
    }
}

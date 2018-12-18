<?php

namespace AppBundle\AdherentMessage\Filter;

use AppBundle\Referent\ManagedUsersFilter;

class ReferentFilterDataObject extends ManagedUsersFilter implements FilterDataObjectInterface
{
    public function serialize()
    {
        return serialize([
            $this->includeAdherentsNoCommittee,
            $this->includeAdherentsInCommittee,
            $this->includeHosts,
            $this->includeSupervisors,
            $this->includeCP,
            $this->queryAreaCode,
            $this->queryCity,
            $this->queryGender,
            $this->queryLastName,
            $this->queryFirstName,
            $this->queryAgeMinimum,
            $this->queryAgeMaximum,
            $this->queryInterests,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->includeAdherentsNoCommittee,
            $this->includeAdherentsInCommittee,
            $this->includeHosts,
            $this->includeSupervisors,
            $this->includeCP,
            $this->queryAreaCode,
            $this->queryCity,
            $this->queryGender,
            $this->queryLastName,
            $this->queryFirstName,
            $this->queryAgeMinimum,
            $this->queryAgeMaximum,
            $this->queryInterests) = unserialize($serialized);
    }
}

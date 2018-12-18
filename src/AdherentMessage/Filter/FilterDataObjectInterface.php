<?php

namespace AppBundle\AdherentMessage\Filter;

use Symfony\Component\HttpFoundation\Request;

interface FilterDataObjectInterface extends \Serializable
{
    public function hasToken(): bool;

    public function getToken(): ?string;

    public function handleRequest(Request $request);
}

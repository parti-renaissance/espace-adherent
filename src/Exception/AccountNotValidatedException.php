<?php

namespace AppBundle\Exception;

use AppBundle\Entity\Adherent;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountNotValidatedException extends AccountStatusException
{
    private $redirect;

    public function __construct(Adherent $adherent, string $redirect, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct('Account not validated.', $code, $previous);

        $this->setUser($adherent);
        $this->redirect = $redirect;
    }

    public function getMessageKey()
    {
        return 'adherent.error.must_be_validated';
    }

    public function getMessageData()
    {
        return [
            'url' => $this->redirect,
        ];
    }

    public function serialize()
    {
        return serialize([
            $this->redirect,
            parent::serialize(),
        ]);
    }

    public function unserialize($str)
    {
        [$this->redirect, $parentData] = unserialize($str);

        parent::unserialize($parentData);
    }
}

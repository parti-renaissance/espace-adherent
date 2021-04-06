<?php

namespace App\Exception;

use App\Entity\Adherent;
use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountNotValidatedException extends AccountStatusException
{
    private $redirect;
    private $messageKey;

    public function __construct(Adherent $adherent, string $redirect = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct('Account not validated.', $code, $previous);

        $this->setUser($adherent);
        $this->redirect = $redirect;
        $this->messageKey = !$adherent->getSource() ? 'adherent.error.must_be_validated' : 'user.error.must_be_validated';
    }

    public function getMessageKey()
    {
        return $this->messageKey;
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
            $this->messageKey,
            parent::serialize(),
        ]);
    }

    public function unserialize($str)
    {
        [$this->redirect, $this->messageKey, $parentData] = unserialize($str);

        parent::unserialize($parentData);
    }
}

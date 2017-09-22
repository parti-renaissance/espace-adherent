<?php

namespace AppBundle\Mailer\Model;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EmailTemplate
{
    /**
     * The message class namespace.
     *
     * @ORM\Column(length=55, unique=true)
     */
    protected $messageClass;

    /**
     * @ORM\Column
     */
    protected $senderEmail;

    /**
     * @ORM\Column(length=100)
     */
    protected $senderName;

    public function __construct(string $messageClass, string $senderEmail, string $senderName)
    {
        $this->messageClass = $messageClass;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    public function getSenderEmail(): string
    {
        return $this->senderEmail;
    }

    public function getSenderName(): string
    {
        return $this->senderName;
    }

    public function getSender(): string
    {
        return sprintf('"%s" <%s>', $this->senderName, $this->senderEmail);
    }

    public function getName(): string
    {
        return Inflector::tableize($this->messageClass);
    }
}

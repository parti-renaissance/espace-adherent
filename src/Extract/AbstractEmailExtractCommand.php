<?php

namespace App\Extract;

use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractEmailExtractCommand
{
    public const FIELD_EMAIL = 'email';

    /**
     * @var string[]|array
     *
     * @Assert\NotBlank
     * @Assert\All({
     *     @Assert\Email(message="{{ value }} n'est pas une adresse mail valide.")
     * })
     */
    private $emails = [];

    /**
     * @var string[]|array
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback="getFieldChoices", multiple=true)
     */
    private $fields = [];

    public function getEmails(): array
    {
        return $this->emails;
    }

    public function setEmails(array $emails): void
    {
        $this->emails = $emails;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    abstract public static function getFieldChoices(): array;
}

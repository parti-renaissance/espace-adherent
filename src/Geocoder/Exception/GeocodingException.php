<?php

namespace App\Geocoder\Exception;

class GeocodingException extends \RuntimeException
{
    private $address;

    public function __construct(string $address, string $message = '', \Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->address = $address;
    }

    public static function create(string $address, \Exception $exception = null): self
    {
        $message = sprintf('Unable to geocode address "%s".', $address);

        return new self($address, $message, $exception);
    }

    public function getAddress(): string
    {
        return $this->address;
    }
}

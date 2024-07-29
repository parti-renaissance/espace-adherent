<?php

namespace App\Validator;

use App\Entity\Geo\Zone;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ZoneType extends Constraint
{
    public string $message = 'Le type de la zone est invalide';

    public array $types = [];

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function __construct($options = null)
    {
        if ($options && !\is_array($options)) {
            $options = ['types' => [$options]];
        } elseif (isset($options['types']) && !\is_array($options['types'])) {
            $options['types'] = [$options['types']];
        }

        parent::__construct($options);

        if (!$this->types) {
            throw new MissingOptionsException(\sprintf('The option "types" must be given for constraint "%s".', __CLASS__), ['types']);
        }

        if (!array_intersect($this->types, Zone::TYPES)) {
            throw new InvalidArgumentException('Invalid types');
        }
    }
}

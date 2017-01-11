<?php

namespace AppBundle\Exception;

/**
 * Thrown when trying to initialize an entity with an uuid.
 */
class InitializedEntityException extends \RuntimeException
{
    private $entity;

    public function __construct($entity, $message = '', $code = 0, \Exception $previous = null)
    {
        if (!method_exists($entity, 'getId')) {
            throw new \LogicException(sprintf('The entity class "%s" must implement "getId".'), get_class($entity));
        }

        parent::__construct(sprintf('The entity of class "%s" is already initialized with id "%s".', get_class($entity), $entity->getId()), $code, $previous);

        $this->entity = $entity;
    }
}

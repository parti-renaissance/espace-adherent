<?php

namespace App\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Puts specific attribute parameter to query parameters and does the same job as DoctrineParamConverter.
 */
class DoctrineQueryStringParamConverter extends DoctrineParamConverter
{
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();

        if (!$request->query->has($name)) {
            throw new \InvalidArgumentException($name.' param is missing in the request');
        }

        $request->attributes->set($name, $request->query->get($name));

        return parent::apply($request, $configuration);
    }
}

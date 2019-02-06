<?php

namespace AppBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;

class ExceptionListener extends BaseExceptionListener
{
    private $apiPathPrefix;

    public function setApiPathPrefix(string $prefix): void
    {
        $this->apiPathPrefix = $prefix;
    }

    protected function setTargetPath(Request $request)
    {
        if (
            $request->isXmlHttpRequest()
            || 0 === mb_strpos($request->getPathInfo(), $this->apiPathPrefix)
            || \in_array('application/json', $request->getAcceptableContentTypes())
        ) {
            return;
        }

        parent::setTargetPath($request);
    }
}

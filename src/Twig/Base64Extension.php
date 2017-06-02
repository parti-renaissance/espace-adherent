<?php

namespace AppBundle\Twig;

class Base64Extension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('base64_decode', 'base64_decode', ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('base64_encode', 'base64_encode', ['is_safe' => ['html']]),
        ];
    }
}

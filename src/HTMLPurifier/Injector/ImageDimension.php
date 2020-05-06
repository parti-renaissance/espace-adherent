<?php

namespace App\HTMLPurifier\Injector;

class ImageDimension extends \HTMLPurifier_Injector
{
    public $name = 'ImageDimension';

    public $needed = ['img' => ['style']];

    private $imageLength;

    public function __construct(string $imageLength)
    {
        $this->imageLength = $imageLength;
    }

    public function handleElement(&$token)
    {
        if ('img' !== $token->name) {
            return;
        }

        $dimension = "max-height:{$this->imageLength};max-width:{$this->imageLength}";

        if (isset($token->attr['style'])) {
            $token->attr['style'] .= $dimension;
        } else {
            $token->attr['style'] = $dimension;
        }
    }
}

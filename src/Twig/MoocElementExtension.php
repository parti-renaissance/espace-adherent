<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Mooc\BaseMoocElement;
use AppBundle\Entity\Mooc\Quiz;
use AppBundle\Entity\Mooc\Video;
use Twig\Extension\AbstractExtension;

class MoocElementExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('mooc_video', function (BaseMoocElement $element) {
                return $element instanceof Video;
            }),
            new \Twig_SimpleTest('mooc_quiz', function (BaseMoocElement $element) {
                return $element instanceof Quiz;
            }),
        ];
    }
}

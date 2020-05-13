<?php

namespace Tests\App\Form\DataTransformer;

use App\Form\DataTransformer\FloatToStringTransformer;
use PHPUnit\Framework\TestCase;

class FloatToStringTransformerTest extends TestCase
{
    /**
     * @dataProvider floatToString
     */
    public function testTransform($float, $string)
    {
        $this->assertSame($string, (new FloatToStringTransformer())->transform($float));
    }

    /**
     * @dataProvider floatToString
     */
    public function testReverseTransform($float, $string)
    {
        $this->assertSame($float, (new FloatToStringTransformer())->reverseTransform($string));
    }

    public function floatToString()
    {
        return [
            [0.1, '0.10'],
            [3.14, '3.14'],
            [(float) 1000, '1000.00'],
        ];
    }

    public function testTransformWithNothing()
    {
        $this->assertSame('', (new FloatToStringTransformer())->transform(null));
    }

    public function testReverseTransformWithNothing()
    {
        $this->assertSame(0.0, (new FloatToStringTransformer())->reverseTransform(null));
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     * @expectedExceptionMessage Expected a float.
     */
    public function testTransformWithNoFloatValue()
    {
        (new FloatToStringTransformer())->transform('42');
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     * @expectedExceptionMessage Expected a string.
     */
    public function testReverseTransformWithNoStringValue()
    {
        (new FloatToStringTransformer())->reverseTransform(42);
    }
}

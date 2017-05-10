<?php

namespace Test\AppBundle\Donation;

use AppBundle\Donation\PayboxPaymentFrequency;

class PayboxPaymentFrequencyTest extends \PHPUnit_Framework_TestCase
{
    public function testFromStringException()
    {
        $this->expectException(\InvalidArgumentException::class);
        PayboxPaymentFrequency::fromString('99');
    }

    public function testFromIntegerException()
    {
        $this->expectException(\InvalidArgumentException::class);
        PayboxPaymentFrequency::fromInteger(99);
    }

    public function testFromString()
    {
        $this->assertEquals(6, PayboxPaymentFrequency::fromString('6')->getFrequency());
    }

    public function testFromInteger()
    {
        $this->assertEquals(6, PayboxPaymentFrequency::fromInteger(6)->getFrequency());
    }

    public function testIsValid()
    {
        $this->assertTrue(PayboxPaymentFrequency::isValid('6'));
        $this->assertTrue(PayboxPaymentFrequency::isValid(2));
        $this->assertFalse(PayboxPaymentFrequency::isValid('enmarche'));
        $this->assertFalse(PayboxPaymentFrequency::isValid('99'));
        $this->assertFalse(PayboxPaymentFrequency::isValid(99));
    }

    public function testGetLabel()
    {
        $this->assertEquals('Pendant 6 mois', PayboxPaymentFrequency::fromInteger(6)->getLabelFrequency());
        $this->assertEquals('Durée illimitée', PayboxPaymentFrequency::fromInteger(0)->getLabelFrequency());
    }

    public function testGetPayboxSuffixCmd()
    {
        $this->assertEquals('PBX_2MONT0000000100PBX_NBPAIE05PBX_FREQ01PBX_QUAND00', PayboxPaymentFrequency::fromInteger(6)->getPayboxSuffixCmd(100.00));
        $this->assertEquals('', PayboxPaymentFrequency::fromInteger(1)->getPayboxSuffixCmd(100.00));
        $this->assertEquals('PBX_2MONT0000000100PBX_NBPAIE00PBX_FREQ01PBX_QUAND00', PayboxPaymentFrequency::fromInteger(0)->getPayboxSuffixCmd(100.00));

        $this->assertEquals('PBX_2MONT0000000100PBX_NBPAIE05PBX_FREQ01PBX_QUAND00', PayboxPaymentFrequency::fromString('6')->getPayboxSuffixCmd(100.00));
        $this->assertEquals('', PayboxPaymentFrequency::fromString('1')->getPayboxSuffixCmd(100.00));
        $this->assertEquals('PBX_2MONT0000000100PBX_NBPAIE00PBX_FREQ01PBX_QUAND00', PayboxPaymentFrequency::fromString('0')->getPayboxSuffixCmd(100.00));
    }
}

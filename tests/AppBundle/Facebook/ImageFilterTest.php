<?php

namespace Tests\AppBundle\Facebook;

use AppBundle\Facebook\ImageFilter;
use Imagine\Gd\Imagine;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class ImageFilterTest extends WebTestCase
{
    protected $imagine;
    protected $picturePath;

    /**
     * @expectedException \RuntimeException
     */
    public function testConstructInvalidPath()
    {
        $tmp = uniqid('invalid_path', false);

        $imageFilter = new ImageFilter($this->imagine, $tmp);

        $imageFilter->applyWatermarks($this->picturePath);

        $this->expectExceptionMessage(sprintf('The %s directory does not exist.', $tmp));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConstructNoFiles()
    {
        $tmp = tempnam(sys_get_temp_dir(), 'watermarks-test');

        $imageFilter = new ImageFilter($this->imagine, $tmp);

        $imageFilter->applyWatermarks($this->picturePath);

        $this->expectExceptionMessage(sprintf('No watermark file found in directory %s', $tmp));
    }

    public function testApplyWatermarks()
    {
        $path = $this->getContainer()->getParameter('env(WATERMARKS_PATH)');

        $imageFilter = new ImageFilter($this->imagine, $path);

        $pictures = $imageFilter->applyWatermarks($this->picturePath);

        $this->assertEquals(5, count($pictures));

        foreach ($pictures as $picture) {
            $this->assertNotFalse(base64_decode($picture));
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->imagine = new Imagine();
        $this->picturePath = $this->getContainer()->getParameter('kernel.root_dir').'/../web/images/organisation/axelle-tessandier.jpg';
    }
}

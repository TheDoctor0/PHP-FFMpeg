<?php

namespace Tests\FFMpeg\Unit\Filters\Video;

use FFMpeg\Filters\Video\ExtractMultipleFramesFilter;
use Tests\FFMpeg\Unit\TestCase;

class ExtractMultipleFramesFilterTest extends TestCase
{
    /**
     * @dataProvider provideFrameRates
     */
    public function testApply($frameRate, $frameFileType, $destinationFolder, $duration, $modulus, $expected)
    {
        $video = $this->getVideoMock();
        $pathfile = '/path/to/file'.mt_rand();

        $format = $this->getMockBuilder('FFMpeg\Format\VideoInterface')->getMock();
        $format->expects($this->any())
            ->method('getModulus')
            ->will($this->returnValue($modulus));

        $filter = new ExtractMultipleFramesFilter($frameRate, $destinationFolder);
        $filter->setFrameFileType($frameFileType);
        $this->assertEquals($expected, $filter->apply($video, $format));
    }

    public function provideFrameRates()
    {
        return [
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_SEC, 'jpg', '/', 100, 2, ['-r', '1/1', '/frame-%d.jpg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_2SEC, 'jpg', '/', 100, 2, ['-r', '1/2', '/frame-%d.jpg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_5SEC, 'jpg', '/', 100, 2, ['-r', '1/5', '/frame-%d.jpg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_10SEC, 'jpg', '/', 100, 2, ['-r', '1/10', '/frame-%d.jpg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_30SEC, 'jpg', '/', 100, 2, ['-r', '1/30', '/frame-%d.jpg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_60SEC, 'jpg', '/', 100, 2, ['-r', '1/60', '/frame-%d.jpg']],

            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_SEC, 'jpeg', '/', 100, 2, ['-r', '1/1', '/frame-%d.jpeg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_2SEC, 'jpeg', '/', 100, 2, ['-r', '1/2', '/frame-%d.jpeg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_5SEC, 'jpeg', '/', 100, 2, ['-r', '1/5', '/frame-%d.jpeg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_10SEC, 'jpeg', '/', 100, 2, ['-r', '1/10', '/frame-%d.jpeg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_30SEC, 'jpeg', '/', 100, 2, ['-r', '1/30', '/frame-%d.jpeg']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_60SEC, 'jpeg', '/', 100, 2, ['-r', '1/60', '/frame-%d.jpeg']],

            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_SEC, 'png', '/', 100, 2, ['-r', '1/1', '/frame-%d.png']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_2SEC, 'png', '/', 100, 2, ['-r', '1/2', '/frame-%d.png']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_5SEC, 'png', '/', 100, 2, ['-r', '1/5', '/frame-%d.png']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_10SEC, 'png', '/', 100, 2, ['-r', '1/10', '/frame-%d.png']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_30SEC, 'png', '/', 100, 2, ['-r', '1/30', '/frame-%d.png']],
            [ExtractMultipleFramesFilter::FRAMERATE_EVERY_60SEC, 'png', '/', 100, 2, ['-r', '1/60', '/frame-%d.png']],
        ];
    }

    public function testInvalidFrameFileType()
    {
        $this->expectException('\FFMpeg\Exception\InvalidArgumentException');
        $filter = new ExtractMultipleFramesFilter('1/1', '/');
        $filter->setFrameFileType('webm');
    }
}

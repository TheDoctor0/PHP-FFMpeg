<?php
/*
 * This file is part of PHP-FFmpeg.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\FFMpeg\Functional;


use FFMpeg\Media\Frames;

class FramesExtractTest extends FunctionalTestCase
{
    /**
     * @param int $framesPerSecond
     * @param int $expectedImages
     *
     * @dataProvider framesProvider
     */
    public function testFramesCount($framesPerSecond, $expectedImages)
    {
        $saveFolder = __DIR__ . '/output/';
        $filenameTemplate = $saveFolder . 'frame-%d.jpg';

        for ($i=1; $i < $expectedImages; $i++) {
            $filename = sprintf($filenameTemplate, $i);
            if (is_file($filename)) {
                unlink($filename);
            }
        }

        $ffmpeg = $this->getFFMpeg();
        $video = $ffmpeg->open(__DIR__ . '/../files/Test.ogv');

        $this->assertInstanceOf('FFMpeg\Media\Video', $video);

        $frames = $video->frames($framesPerSecond);

        $frames->save($saveFolder);
        for ($i=1; $i < $expectedImages; $i++) {
            $filename = sprintf($filenameTemplate, $i);
            $this->assertFileExists($filename);
            unlink($filename);
        }

        // next frame was not fetched
        $this->assertFileDoesNotExist(sprintf($filenameTemplate, $i));
    }

    public function framesProvider()
    {
        $calls = array();
        $calls[] = array(Frames::FRAMERATE_EVERY_2SEC, 17);
        $calls[] = array(Frames::FRAMERATE_EVERY_SEC, 32);

        return $calls;
    }
}

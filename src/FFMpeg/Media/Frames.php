<?php

/*
 * This file is part of PHP-FFmpeg.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FFMpeg\Media;

use Alchemy\BinaryDriver\Exception\ExecutionFailureException;
use FFMpeg\Exception\InvalidArgumentException;
use FFMpeg\Driver\FFMpegDriver;
use FFMpeg\FFProbe;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\Filters\Frames\FramesFilterInterface;
use FFMpeg\Filters\Frames\FramesFilters;

class Frames extends AbstractMediaType
{
    /**
     * will extract a frame every second
     */
    const FRAMERATE_EVERY_SEC = '1/1';

    /**
     * will extract a frame every 2 seconds
     */
    const FRAMERATE_EVERY_2SEC = '1/2';

    /**
     * will extract a frame every 5 seconds
     */
    const FRAMERATE_EVERY_5SEC = '1/5';

    /**
     * will extract a frame every 10 seconds
     */
    const FRAMERATE_EVERY_10SEC = '1/10';

    /**
     * will extract a frame every 30 seconds
     */
    const FRAMERATE_EVERY_30SEC = '1/30';

    /**
     * will extract a frame every minute
     */
    const FRAMERATE_EVERY_60SEC = '1/60';

    /**
     * @var string
     */
    private $frameRate;

    /**
     * @var string
     */
    private $frameFileType = 'jpg';

    /**
     * @var Video
     */
    private $video;

    /**
     * @var array
     */
    private static $supportedFrameFileTypes = array('jpg', 'jpeg', 'png');

    public function __construct(Video $video, FFMpegDriver $driver, FFProbe $ffprobe, $frameRate = self::FRAMERATE_EVERY_SEC)
    {
        parent::__construct($video->getPathfile(), $driver, $ffprobe);
        $this->video = $video;
        $this->frameRate = $frameRate;
    }

    /**
     * @param string $frameFileType
     * @return Frames
     *
     * @throws InvalidArgumentException
     */
    public function setFrameFileType($frameFileType)
    {
        if (in_array($frameFileType, self::$supportedFrameFileTypes, true)) {
            $this->frameFileType = $frameFileType;
            return $this;
        }

        throw new InvalidArgumentException('Invalid frame file type, use: ' . implode(',', self::$supportedFrameFileTypes));
    }

    /**
     * {@inheritdoc}
     *
     * @return FramesFilters
     */
    public function filters()
    {
        return new FramesFilters($this);
    }

    /**
     * {@inheritdoc}
     *
     * @return self
     */
    public function addFilter(FramesFilterInterface $filter)
    {
        $this->filters->add($filter);

        return $this;
    }

    /**
     * Saves frames to the $destinationFolder
     *
     * @param string $destinationFolder
     *
     * @return self
     *
     * @throws RuntimeException
     */
    public function save($destinationFolder = __DIR__)
    {
        /**
         * @see https://trac.ffmpeg.org/wiki/Create%20a%20thumbnail%20image%20every%20X%20seconds%20of%20the%20video
         */

        $destinationFolder = rtrim($destinationFolder, '/') . '/';
        $commands = array('-y', '-i', $this->pathfile);
        $commands[] = '-r';
        $commands[] = $this->frameRate;
        $commands[] = $destinationFolder . 'frame-%d.' . $this->frameFileType;

        foreach ($this->filters as $filter) {
            $commands = array_merge($commands, $filter->apply($this));
        }

        try {
            $this->driver->command($commands);
        } catch (ExecutionFailureException $e) {
            $pattern = $destinationFolder . 'frame-*.' . $this->frameFileType;
            foreach (glob($pattern) as $file) {
                $this->cleanupTemporaryFile($file);
            }
            throw new RuntimeException('Unable to save frames', $e->getCode(), $e);
        }

        return $this;
    }
}

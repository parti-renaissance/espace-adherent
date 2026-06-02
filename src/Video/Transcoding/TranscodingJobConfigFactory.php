<?php

declare(strict_types=1);

namespace App\Video\Transcoding;

use App\Entity\Video;
use Google\Cloud\Video\Transcoder\V1\AudioStream;
use Google\Cloud\Video\Transcoder\V1\ElementaryStream;
use Google\Cloud\Video\Transcoder\V1\JobConfig;
use Google\Cloud\Video\Transcoder\V1\Manifest;
use Google\Cloud\Video\Transcoder\V1\Manifest\ManifestType;
use Google\Cloud\Video\Transcoder\V1\MuxStream;
use Google\Cloud\Video\Transcoder\V1\SpriteSheet;
use Google\Cloud\Video\Transcoder\V1\VideoStream;
use Google\Cloud\Video\Transcoder\V1\VideoStream\H264CodecSettings;
use Google\Protobuf\Duration;

/**
 * Builds the inline Transcoder job configuration. Output file names must match Video::FILE_NAME_*:
 * a single-rendition HLS manifest (master.m3u8), an MP4 preview (preview.mp4) and a thumbnail
 * (thumbnail0000000000.jpeg). No edit list is used so short source clips never fail on a fixed trim.
 */
class TranscodingJobConfigFactory
{
    private const string VIDEO_STREAM_KEY = 'video-stream';
    private const string AUDIO_STREAM_KEY = 'audio-stream';
    private const string HLS_MUX_KEY = 'hls-stream';
    private const string PREVIEW_MUX_KEY = 'preview';
    private const string THUMBNAIL_FILE_PREFIX = 'thumbnail';

    public function create(): JobConfig
    {
        return new JobConfig([
            'elementary_streams' => [
                new ElementaryStream([
                    'key' => self::VIDEO_STREAM_KEY,
                    'video_stream' => new VideoStream([
                        'h264' => new H264CodecSettings([
                            'height_pixels' => 720,
                            'width_pixels' => 0, // 0 keeps the source aspect ratio (portrait, square or landscape)
                            'bitrate_bps' => 2_000_000,
                            'frame_rate' => 30,
                            'pixel_format' => 'yuv420p',
                            'rate_control_mode' => 'vbr',
                            'crf_level' => 21,
                            'gop_duration' => new Duration(['seconds' => 2]),
                            'vbv_size_bits' => 2_000_000,
                            'vbv_fullness_bits' => 1_800_000,
                            'entropy_coder' => 'cabac',
                            'profile' => 'main',
                            'preset' => 'veryfast',
                        ]),
                    ]),
                ]),
                new ElementaryStream([
                    'key' => self::AUDIO_STREAM_KEY,
                    'audio_stream' => new AudioStream([
                        'codec' => 'aac',
                        'bitrate_bps' => 128_000,
                        'channel_count' => 2,
                        'sample_rate_hertz' => 48_000,
                    ]),
                ]),
            ],
            'mux_streams' => [
                new MuxStream([
                    'key' => self::PREVIEW_MUX_KEY,
                    'file_name' => Video::FILE_NAME_PREVIEW,
                    'container' => 'mp4',
                    'elementary_streams' => [self::VIDEO_STREAM_KEY, self::AUDIO_STREAM_KEY],
                ]),
                new MuxStream([
                    'key' => self::HLS_MUX_KEY,
                    'container' => 'ts',
                    'elementary_streams' => [self::VIDEO_STREAM_KEY, self::AUDIO_STREAM_KEY],
                ]),
            ],
            'manifests' => [
                new Manifest([
                    'file_name' => Video::FILE_NAME_HLS,
                    'type' => ManifestType::HLS,
                    'mux_streams' => [self::HLS_MUX_KEY],
                ]),
            ],
            'sprite_sheets' => [
                new SpriteSheet([
                    'file_prefix' => self::THUMBNAIL_FILE_PREFIX, // produces Video::FILE_NAME_THUMBNAIL
                    'sprite_height_pixels' => 480, // width auto-derived from the source aspect ratio
                    'total_count' => 1,
                ]),
            ],
        ]);
    }
}

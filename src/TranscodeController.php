<?php

use AC\Component\Transcoding\Transcoder;
use AC\Component\Transcoding\Event\TranscodeEvents;

/** Andrew's first test controller
 *
 * @package Slimfra
 * @author Andrew Freix
 */
class TranscodeController extends \Slimfra\Controller {
	private $handbrake_path = "C:\\Users\\Andrew\\Downloads\\HandBrake-0.9.6-x86_64-Win_CLI\\HandbrakeCLI.exe";
	private $ffmpeg_path = "C:\\Users\\Andrew\\Downloads\\ffmpeg-20120720-git-85761ef-win64-static\\ffmpeg-20120720-git-85761ef-win64-static\bin\\ffmpeg.exe";

	public function registerPresets(Transcoder $transcoder) {
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\ClassicPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\UniversalPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\iPodPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\iPhoneiPodTouchPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\iPadPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\iPhone4Preset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\AppleTVPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\AppleTV2Preset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\AppleTVLegacyPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\iPhoneLegacyPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\iPodLegacyPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\NormalPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\Handbrake\HighProfilePreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\SoundFromVideoPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AviToAnimatedGifPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\ConvertNonMVideoPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression32kPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression96kPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression128kPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression160kPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression192kPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression256kPreset);
		$transcoder->registerPreset(new AC\Component\Transcoding\Preset\ffmpeg\AudioCompression320kPreset);
	}
	
	public function registerAdapters(Transcoder $transcoder) {
		$transcoder->registerAdapter(new AC\Component\Transcoding\Adapter\HandbrakeAdapter($this->handbrake_path));
		$transcoder->registerAdapter(new AC\Component\Transcoding\Adapter\ffmpegAdapter($this->ffmpeg_path));
	}
    /*
	 * Tests a transcode preset with the give preset_key (a string)
	 */
    public function testPreset($preset_key) {
        $transcoder = new Transcoder;
		
		$this->registerAdapters($transcoder);
		$this->registerPresets($transcoder);

		$inputFilePath = "input\\movie.avi";
		$outputFilePath = "output\\";

		//The listener currently doesn't work -8/28/12
		$transcoder->addListener(TranscodeEvents::MESSAGE, function($e, $dsalk) {
			echo($e->getMessage()."<br><br>");
		});
		$newFile = $transcoder->transcodeWithPreset($inputFilePath, $preset_key, $outputFilePath, Transcoder::ONCONFLICT_INCREMENT, Transcoder::ONDIR_CREATE);
		return $newFile->getPathname();
    }
}


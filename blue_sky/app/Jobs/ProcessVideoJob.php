<?php

namespace App\Jobs;

use App\Models\Media;
use App\ProcessVideo;
use App\Service\Utils;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 25;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    protected $videoPath;
    protected $pathTemp;
    protected $extension;
    protected $regionId;
    protected $resolutionScale1080;
    protected $resolutionScale320;
    // protected $utils;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct($videoPath, $pathTemp, $extension, $regionId)
    {
        $this->videoPath = $videoPath;
        $this->pathTemp = $pathTemp;
        $this->extension = $extension;
        $this->regionId = $regionId;
        // $this->utils = $utils;
        // $this->resolutionScale1080 = $resolutionScale1080;
        // $this->resolutionScale320 = $resolutionScale320;
    }

    public function handle(ProcessVideo $processVideo)
    {
        try {
            DB::beginTransaction();

            $result = $processVideo->video(
                $this->videoPath,
                $this->pathTemp,
                $this->extension,
                $this->regionId,
                // $this->resolutionScale1080,
                // $this->resolutionScale320
            );

            $data = $result->getData(true);

            if ($data['success'] === false) {
                Log::error('Erro ao processar vídeo: ' . json_encode($data));
                DB::rollBack();
                return;
            }

            $dadosMidia = $data['data'];

            Media::create([
                'media_link_original' => $dadosMidia['media_link_original'],
                'media_link_1080' => $dadosMidia['media_link_1080'],
                'media_link_320' => $dadosMidia['media_link_320'],
                'fk_region_id' => $dadosMidia['fk_region_id'],
            ]);

            DB::commit();

            // $this->utils->zabbix();
            
        } catch (\Throwable $e) {

            DB::rollBack();
            Log::error('Erro ao executar ProcessVideoJob: ' . $e->getMessage());
        }
    }
}
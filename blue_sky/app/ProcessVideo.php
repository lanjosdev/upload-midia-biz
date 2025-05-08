<?php

namespace App;

use App\Jobs\ProcessVideoJob;
use App\Models\Media;
use App\Models\Region;
use App\Models\User;
use App\Service\Utils;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProcessVideo
{
    protected $media;
    protected $utils;
    /**
     * Create a new class instance.
     */
    public function __construct(Media $media, Utils $utils)
    {
        $this->media = $media;
        $this->utils = $utils;
    }

    public function video($videoPath, $pathTemp, $extension, $regionId)
    {

        $originalPath = null;
        $fileName = null;
        $destinationPath1080 = null;
        $destinationPath320 = null;
        $infoUsersLocationUf = null;

        try {

            // $user = $request->user();

            // $validatedData = $request->validate(
            //     $this->media->rulesMedias(),
            //     $this->media->feedbackMedias()
            // );

            //Linux "/usr/bin/ffmpeg" and '/usr/bin/ffprobe';
            //Windows (my computer) C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffprobe.exe and ffmpeg.exe;

            if (PHP_OS_FAMILY == 'Windows') {
                $ffprobePath = 'C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffprobe.exe';
                $ffmpegPath = "C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffmpeg.exe";
            } else {
                // $ffprobePath = '/home/ubuntu/Projetos/midiaapi/backend/midia_api_video/blue_sky/bin/ffprobe';
                // $ffmpegPath = '/home/ubuntu/Projetos/midiaapi/backend/midia_api_video/blue_sky/bin/ffmpeg';
                $ffprobePath = '/usr/local/bin/ffprobe';
                $ffmpegPath = '/usr/local/bin/ffmpeg';
            }

            // if ($validatedData) {

            // $infoUsersLocationUf = User::where('email', $user->email)
            //     ->first();

            if ($regionId) {
                if ($regionId == 1) {
                    $infoUsersLocationUf = 'CE';
                } elseif ($regionId == 2) {
                    $infoUsersLocationUf = 'PE';
                } else {
                    $infoUsersLocationUf = 'RJ';
                }
                // }

                $video = $videoPath;

                // $extension = explode('.', $video);

                // $parts = explode('.', $video);
                // $extension = end($parts);

                $pathTmp = $pathTemp;

                if ($extension == 'MOV') {
                    // Cria o caminho de saída temporário
                    $outputPath = storage_path('app/temp/' . uniqid() . '.mp4');

                    // Garante que a pasta temp exista
                    if (!File::exists(storage_path('app/temp'))) {
                        File::makeDirectory(storage_path('app/temp'), 0775, true);
                    }

                    $cmd = "$ffmpegPath -i \"$pathTmp\" -c:v libx264 -crf 18 -c:a aac -b:a 128k -movflags +faststart \"$outputPath\" 2>&1";

                    $execOutput = shell_exec($cmd);

                    Log::debug('FFmpeg Output: ' . $execOutput);

                    // Atualiza o caminho temporário para o novo arquivo gerado
                    $pathTmp = $outputPath;
                    $extension = 'mp4';
                }


                $date = now()->format('d-m-Y_H-i-s');
                $fileName = $infoUsersLocationUf . '_' . $date . '_' . uniqid() . '.' . $extension;

                $destinationPathOriginal = public_path('videos/original');
                $destinationPath1080 = public_path('videos/videos_1080');
                $destinationPath320 = public_path('videos/videos_320');

                foreach ([$destinationPathOriginal, $destinationPath1080, $destinationPath320] as $path) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0775, true);
                    }
                }

                $originalPath = null;

                $originalPath = $destinationPathOriginal . DIRECTORY_SEPARATOR . $fileName;
                // $video->move($destinationPathOriginal, $fileName);
                $video = $videoPath;

                if (!File::exists($video)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Arquivo não encontrado: $video"
                    ]);
                }

                File::move($video, $destinationPathOriginal . DIRECTORY_SEPARATOR . $fileName);

                $cmdGetDuration = "$ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 \"$originalPath\" 2>&1";

                $duration = floatval(trim(shell_exec($cmdGetDuration)));

                if ($duration < 10) {
                    return response()->json([
                        'success' => false,
                        'message' => 'O vídeo precisa ter no mínimo 10 segundos.',
                    ]);
                }

                $molduraPath = null;

                if ($infoUsersLocationUf === 'CE') {
                    $molduraPath = public_path('fortaleza1080.mov');
                    $moldura320Path = public_path('fortaleza448_2.mov');
                } elseif ($infoUsersLocationUf === 'PE') {
                    $molduraPath = public_path('recife1080.mov');
                    $moldura320Path = public_path('recife448_2.mov');
                } else {
                    $molduraPath = public_path('rj1080.mov');
                    $moldura320Path = public_path('rj448_2.mov');
                }

                $withFramePath = $destinationPathOriginal . '/temp_framed_' . $fileName;

                $cmdFrame = "$ffmpegPath -y -i \"$originalPath\" -t 10 -r 30 -an -c:v libx264 -maxrate 10000k -bufsize 20000k -preset fast \"$withFramePath\"";
                shell_exec($cmdFrame);



                // gerar resoluções (1080p e 320p)
                ////////////////////////// $cmd1080 = "$ffmpegPath -y -i \"$withFramePath\" -i \"$molduraPath\" -filter_complex \"[0:v][1:v] overlay=0:0,scale=$resolutionScale1080\" \"$destinationPath1080/$fileName\"";
                // $cmd320 = "$ffmpegPath -y -noautorotate -i \"$withFramePath\" -i \"$moldura320Path\" -filter_complex \"[0:v]scale=320:480,crop=320:448:0:0[scaled];[scaled][1:v]overlay=0:0\" -b:v 9000k -minrate 9000k -maxrate 10000k -x264-params nal-hrd=cbr -bufsize 20000k -fs 20M -preset ultrafast \"$destinationPath320/$fileName\"";
                // $cmd1080 = "$ffmpegPath -y -noautorotate -i \"$withFramePath\" -i \"$molduraPath\" -filter_complex \"[0:v]scale=1080:1920:[scaled];[scaled][1:v]overlay=0:0\" -b:v 9000k -minrate 9000k -maxrate 10000k -x264-params nal-hrd=cbr -bufsize 20000k -fs 20M -preset ultrafast \"$destinationPath1080/$fileName\"";

                // $exit = "[1:v]format=rgba[moldura_alpha]; " .
                //     "[bg][moldura_alpha]overlay=0:0[out]\" " .
                //     "-map \"[out]\" -t 10 -r 30 -an -c:v libx264 " .
                //     "-preset fast ";

                $destinationPath1080 = $destinationPath1080 . DIRECTORY_SEPARATOR . $fileName;
                
                $cmd1080 = "$ffmpegPath -y " .
                    "-i \"$withFramePath\" " .               // input 0: vídeo de fundo
                    "-i \"$molduraPath\" " .                 // input 1: moldura com alpha
                    "-filter_complex \"" .
                    "[0:v]scale=1080:1920[bg]; " .
                    "[1:v]format=rgba[moldura_alpha]; " .
                    "[bg][moldura_alpha]overlay=0:0[out]\" " .
                    "-map \"[out]\" -t 10 -r 30 -an -c:v libx264 " .
                    // "-b:v 9000k -minrate 9000k -maxrate 10000k " .
                    // "-x264-params nal-hrd=cbr -bufsize 20000k -fs 20M " .
                    // "-maxrate 10000k " .
                    // "-bufsize 20000k " .
                    "-preset fast " .
                    // "-preset veryslow " .
                    "\"$destinationPath1080\"";
                    
                    
                // $cmd1080 = "$ffmpegPath -y " .
                //     "-i \"$withFramePath\" " .
                //     "-i \"$molduraPath\" " .
                //     "-filter_complex \"" .
                //     "[0:v]scale=1080:1920[bg]; " .
                //     "$exit " .
                //     // "-preset veryslow " .
                //     "\"$destinationPath1080/$fileName\"";
                
                // $cmd320 = "$ffmpegPath -y " .
                //     "-i \"$withFramePath\" " .
                //     "-i \"$moldura320Path\" " .
                //     "-filter_complex \"" .
                //     "[0:v]scale=320:480,crop=320:448:0:16[bg]; " .
                //     "$exit " .
                //     // "-preset veryslow " .
                //     "\"$destinationPath320/$fileName\"";
                
                shell_exec($cmd1080);
                
                // $destinationPath320 = $destinationPath320 . DIRECTORY_SEPARATOR . $fileName;
                
                // $cmd320 = "$ffmpegPath -y " .
                // "-i \"$withFramePath\" " .
                // "-i \"$moldura320Path\" " .
                // "-filter_complex \"" .
                //     "[0:v]scale=320:480,crop=320:448:0:16[bg]; " .
                //     "[1:v]format=rgba[moldura_alpha]; " .
                //     "[bg][moldura_alpha]overlay=0:0[out]\" " .
                //     "-map \"[out]\" -t 10 -r 30 -an " .
                //     "-c:v libx264 " .
                //     // "-b:v 9000k -minrate 9000k -maxrate 10000k " .
                //     // "-x264-params nal-hrd=cbr -bufsize 20000k -fs 20M " .
                //     // "-maxrate 10000k " .
                //     // "-bufsize 20000k " .
                //     "-preset fast " .
                //     // "-preset veryslow " .
                //     "\"$destinationPath320\"";

                // shell_exec($cmd320);

                if (file_exists($destinationPath1080) /*&& file_exists($destinationPath320)*/) {
                    Log::info('existe e vai enviar');
                    $pathOriginal = DIRECTORY_SEPARATOR . 'v1' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'original' . DIRECTORY_SEPARATOR . $fileName;
                    $path1080 = DIRECTORY_SEPARATOR . 'v1' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'videos_1080' . DIRECTORY_SEPARATOR . $fileName;
                    $path320 = DIRECTORY_SEPARATOR . 'v1' . DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'videos_320' . DIRECTORY_SEPARATOR . $fileName;

                    $data = [
                        'media_link_original' => asset($pathOriginal),
                        'media_link_1080' => asset($path1080),
                        'media_link_320' => 'asset($path320)',
                        'fk_region_id' => $regionId,
                    ];

                    if ($data) {

                        return response()->json([
                            'success' => true,
                            'message' => 'success.',
                            'data' => $data
                        ]);
                    }
                }else {
                    Log::info('algo de errado aconteceu');
                }
                // shell_exec($cmd320);

                // if (file_exists($destinationPath1080 . '/' . $fileName) && file_exists($destinationPath320 . '/' . $fileName)) {

                //     if (file_exists($withFramePath)) {
                //         unlink($withFramePath);
                //     }

                //     $data = [
                //         'media_link_original' => asset("/v1/videos/original/$fileName"),
                //         'media_link_1080' => asset("/v1/videos/videos_1080/$fileName"),
                //         'media_link_320' => asset("/v1/videos/videos_320/$fileName"),
                //         'fk_region_id' => $regionId,
                //     ];

                //     if ($data) {

                //         if (File::exists($video)) {
                //             unlink($video);
                //         }

                //         return response()->json([
                //             'success' => true,
                //             'message' => 'success.',
                //             'data' => $data
                //         ]);
                //     }
                // } else {

                //     $this->utils->deleteFilesIfExist($originalPath, $fileName, $destinationPath1080, $destinationPath320, $video);

                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Erro ao gerar as resoluções do vídeo.',
                //     ]);
                // }

                // Log::info($destinationPath1080 . DIRECTORY_SEPARATOR . $fileName);
                // Log::info(file_exists($destinationPath1080 . DIRECTORY_SEPARATOR . $fileName));

                // Log::info($destinationPath320 . DIRECTORY_SEPARATOR . $fileName);
                // Log::info(file_exists($destinationPath320 . DIRECTORY_SEPARATOR . $fileName));

                // if (!file_exists($destinationPath1080 . DIRECTORY_SEPARATOR . $fileName) || !file_exists($destinationPath320 . DIRECTORY_SEPARATOR . $fileName)) {

                //     $this->utils->deleteFilesIfExist($originalPath, $fileName, $destinationPath1080, $destinationPath320, $video);

                //     return response()->json([
                //         'success' => false,
                //         'message' => 'Erro ao gerar as resoluções do vídeo.',
                //     ]);
                // }

                // if (file_exists($withFramePath)) {
                //     unlink($withFramePath);
                // }

                // $data = [
                //     'media_link_original' => asset("/v1/videos/original/$fileName"),
                //     'media_link_1080' => asset("/v1/videos/videos_1080/$fileName"),
                //     'media_link_320' => asset("/v1/videos/videos_320/$fileName"),
                //     'fk_region_id' => $regionId,
                // ];

                // if ($data) {

                //     if (File::exists($video)) {
                //         unlink($video);
                //     }

                //     return response()->json([
                //         'success' => true,
                //         'message' => 'success.',
                //         'data' => $data
                //     ]);
                // }
            }
        } catch (QueryException $qe) {

            $this->utils->deleteFilesIfExist($originalPath, $fileName, $destinationPath1080, $destinationPath320, $video);

            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {

            $this->utils->deleteFilesIfExist($originalPath, $fileName, $destinationPath1080, $destinationPath320, $video);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
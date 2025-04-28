<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use App\Service\Utils;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class MediaController extends Controller
{
    protected $media;
    protected $utils;

    public function __construct(Media $media, Utils $utils)
    {
        $this->media = $media;
        $this->utils = $utils;
    }

    public function getMedia(Request $request)
    {
        try {
            $data = Media::orderBy('created_at', 'desc')
                ->paginate(50);

            $data->getCollection()->transform(function ($media) {
                return [
                    'id' => $media->id,
                    'media_original' => $media->media_link_original,
                    'media_1080x1920' => $media->media_link_1080,
                    'media_320x448' => $media->media_link_320,
                    'region' => optional($media->region)->state_uf ?? null,
                    'created_at' => $this->utils->formattedDate($media, 'created_at') ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data recovered successfully',
                'data' => $data,
            ]);
        } catch (QueryException $qe) {

            Log::info('Error DB: ' . $qe->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something unexpected happened, please try again later',
            ]);
        } catch (Exception $e) {

            Log::info('Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something unexpected happened, please try again later',
            ]);
        }
    }

    public function upload(Request $request)
    {
        DB::beginTransaction();

        $originalPath = null;
        $fileName = null;
        $destinationPath1080 = null;
        $destinationPath320 = null;
        try {

            $user = $request->user();

            $validatedData = $request->validate(
                $this->media->rulesMedias(),
                $this->media->feedbackMedias()
            );

            //Linux "/usr/bin/ffmpeg" and '/usr/bin/ffprobe';  
            //Windows (my computer) C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffprobe.exe and ffmpeg.exe;

            if (PHP_OS_FAMILY == 'Windows') {
                $ffprobePath = 'C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffprobe.exe';
                $ffmpegPath = "C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffmpeg.exe";
            } else {
                $ffprobePath = '/usr/bin/ffprobe';
                $ffmpegPath = '/usr/bin/ffmpeg';
            }

            if ($validatedData) {

                $infoUsersLocationUf = User::where('email', $user->email)
                    ->first();

                if ($infoUsersLocationUf) {
                    if ($infoUsersLocationUf->fk_region_id == 1) {
                        $infoUsersLocationUf = 'CE';
                    } elseif ($infoUsersLocationUf->fk_region_id == 2) {
                        $infoUsersLocationUf = 'PE';
                    } else {
                        $infoUsersLocationUf = 'RJ';
                    }
                }

                $video = $request->file('video');

                $extension = $video->getClientOriginalExtension();
                $pathTmp = $video->getPathname();

                if ($extension == 'MOV') {
                    // Cria o caminho de saída temporário
                    $outputPath = storage_path('app/temp/' . uniqid() . '.mp4');

                    // Garante que a pasta temp exista
                    if (!File::exists(storage_path('app/temp'))) {
                        File::makeDirectory(storage_path('app/temp'), 0775, true);
                    }

                    $cmd = "$ffmpegPath -i \"$pathTmp\" -c:v libx264 -crf 18 -c:a aac -b:a 128k -movflags +faststart -an -r 30 \"$outputPath\" 2>&1";

                    $execOutput = shell_exec($cmd);

                    // Aqui você pode fazer um Log::debug para ver o que o ffmpeg retornou
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
                $video->move($destinationPathOriginal, $fileName);

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
                    $molduraPath = public_path('Recife.png');
                    $moldura320Path = public_path('pe.png');
                } elseif ($infoUsersLocationUf === 'PE') {
                    $molduraPath = public_path('Fortal.png');
                    $moldura320Path = public_path('ce.png');
                } else {
                    $molduraPath = public_path('RJ.png');
                    $moldura320Path = public_path('rjmin.png');
                }

                $withFramePath = $destinationPathOriginal . '/temp_framed_' . $fileName; // Não será salvo, será usado apenas para gerar as resoluções
                $cmdFrame = "$ffmpegPath -y -i \"$originalPath\" -t 10 -r 30 -an -c:v libx264 -preset ultrafast -c:a copy \"$withFramePath\"";
                shell_exec($cmdFrame);

                // gerar resoluções (1080p e 320p)
                $cmd1080 = "$ffmpegPath -y -i \"$withFramePath\" -i \"$molduraPath\" -filter_complex \"[0:v][1:v] overlay=0:0,scale=1080:1920\" \"$destinationPath1080/$fileName\"";
                $cmd320 = "$ffmpegPath -y -i \"$withFramePath\" -i \"$moldura320Path\" -filter_complex \"[0:v]scale=320:480,crop=320:448:0:0[scaled];[scaled][1:v]overlay=0:0\" \"$destinationPath320/$fileName\"";

                shell_exec($cmd1080);
                shell_exec($cmd320);

                if (!file_exists($destinationPath1080 . DIRECTORY_SEPARATOR . $fileName) || !file_exists($destinationPath320 . DIRECTORY_SEPARATOR . $fileName)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao gerar as resoluções do vídeo.',
                    ]);
                }

                if (file_exists($withFramePath)) {
                    unlink($withFramePath);
                }

                $insertMedia = Media::create([
                    'media_link_original' => asset("videos/original/$fileName"),
                    'media_link_1080' => asset("videos/videos_1080/$fileName"),
                    'media_link_320' => asset("videos/videos_320/$fileName"),
                    'fk_region_id' => $user->fk_region_id,
                ]);

                if ($insertMedia) {

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Vídeo processado com sucesso.',
                    ]);
                }
            }
        } catch (QueryException $qe) {
            DB::rollBack();

            $pathsToDelete = [];

            if (!empty($originalPath) && file_exists($originalPath)) {
                $pathsToDelete[] = $originalPath;
            }
            if (!empty($fileName) && !empty($destinationPath1080) && file_exists($destinationPath1080 . DIRECTORY_SEPARATOR . $fileName)) {
                $pathsToDelete[] = $destinationPath1080 . DIRECTORY_SEPARATOR . $fileName;
            }
            if (!empty($fileName) && !empty($destinationPath320) && file_exists($destinationPath320 . DIRECTORY_SEPARATOR . $fileName)) {
                $pathsToDelete[] = $destinationPath320 . DIRECTORY_SEPARATOR . $fileName;
            }

            foreach ($pathsToDelete as $path) {
                unlink($path);
            }


            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            $pathsToDelete = [];

            if (!empty($originalPath) && file_exists($originalPath)) {
                $pathsToDelete[] = $originalPath;
            }
            if (!empty($fileName) && !empty($destinationPath1080) && file_exists($destinationPath1080 . DIRECTORY_SEPARATOR . $fileName)) {
                $pathsToDelete[] = $destinationPath1080 . DIRECTORY_SEPARATOR . $fileName;
            }
            if (!empty($fileName) && !empty($destinationPath320) && file_exists($destinationPath320 . DIRECTORY_SEPARATOR . $fileName)) {
                $pathsToDelete[] = $destinationPath320 . DIRECTORY_SEPARATOR . $fileName;
            }

            foreach ($pathsToDelete as $path) {
                unlink($path);
            }


            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
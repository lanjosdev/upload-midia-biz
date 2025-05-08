<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoJob;
use App\Models\Media;
use App\Models\Region;
use App\Models\User;
use App\ProcessVideo;
use App\Service\Utils;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class MediaController extends Controller
{
    protected $media;
    protected $utils;
    protected $processVideo;

    public function __construct(Media $media, Utils $utils, ProcessVideo $processVideo)
    {
        $this->media = $media;
        $this->utils = $utils;
        $this->processVideo = $processVideo;
    }

    public function getMedia(Request $request)
    {
        try {
            $resolutionsRequest = $request->has('resolution')
                ? explode(',', $request->input('resolution'))
                : null;

            $limit = $request->has('limit') && !empty($request->input('limit'))
                ? intval($request->input('limit'))
                : 50;

            $data = Media::orderBy('created_at', 'desc')

                // filtro por data inicial    
                ->when($request->has('start_time'), function ($query) use ($request) {
                    $query->where('created_at', '>=', $request->input('start_time'));
                })

                // filtro por data final
                ->when($request->has('end_time'), function ($query) use ($request) {
                    $query->where('created_at', '<=', $request->input('end_time'));
                })

                // filtro por uf
                ->when($request->has('uf'), function ($query) use ($request) {
                    $uf = explode(',', $request->input('uf'));
                    $regionIds = Region::whereIn('state_uf', $uf)->pluck('id');
                    $query->whereIn('fk_region_id', $regionIds);
                })

                ->limit($limit)
                ->get();
            //paginate(50)
            // ->appends($request->only(['uf', 'start_time', 'end_time', 'resolution']));

            $data->transform(function ($media) use ($resolutionsRequest) {
                $allMedias = [
                    ['resolution' => 'original', 'url' => $media->media_link_original],
                    ['resolution' => '1080p', 'url' => $media->media_link_1080],
                    ['resolution' => '320p', 'url' => $media->media_link_320],
                ];

                if ($resolutionsRequest) {
                    $allMedias = array_filter($allMedias, function ($item) use ($resolutionsRequest) {
                        return in_array($item['resolution'], $resolutionsRequest) && !empty($item['url']);
                    });
                } else {
                    $allMedias = array_filter($allMedias, function ($item) {
                        return !empty($item['url']);
                    });
                }

                $allMedias = array_values($allMedias);

                return [
                    'id' => $media->id,
                    'uf' => optional($media->region)->state_uf ?? null,
                    'created_at' => $this->utils->formattedDate($media, 'created_at') ?? null,
                    'medias' => $allMedias,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'dados recuperados com sucesso.',
                'data' => $data,
                'count_data' => count($data)
            ]);
        } catch (QueryException $qe) {

            Log::error('Error DB: ' . $qe->getMessage());


            return response()->json([
                'success' => false,
                'message' => 'Algo de errado aconteceu. Por favor, tente novamente mais tarde.',
            ]);
        } catch (Exception $e) {

            Log::error('Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Algo de errado aconteceu. Por favor, tente novamente mais tarde.',
            ]);
        }
    }

    public function uploadPrimary(Request $request)
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

                    // $cmd = "$ffmpegPath -i \"$pathTmp\" -c:v libx264 -crf 18 -c:a aac -b:a 128k -movflags +faststart -an -r 30 \"$outputPath\" 2>&1";
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
                    $molduraPath = public_path('fortaleza1080.mov');
                    $moldura320Path = public_path('fortaleza448.mov');
                } elseif ($infoUsersLocationUf === 'PE') {
                    $molduraPath = public_path('recife1080.mov');
                    $moldura320Path = public_path('recife448.mov');
                } else {
                    $molduraPath = public_path('rj1080.mov');
                    $moldura320Path = public_path('rj448.mov');
                }

                $withFramePath = $destinationPathOriginal . '/temp_framed_' . $fileName; // Não será salvo, será usado apenas para gerar as resoluções
                $cmdFrame = "$ffmpegPath -y -noautorotate -i \"$originalPath\" -t 10 -r 30 -an -c:v libx264 -preset ultrafast -c:a copy \"$withFramePath\"";
                shell_exec($cmdFrame);

                // gerar resoluções (1080p e 320p)
                // $cmd1080 = "$ffmpegPath -y -noautorotate -i \"$withFramePath\" -i \"$molduraPath\" -filter_complex \"[0:v][1:v] overlay=0:0,scale=1080:1920\" -preset ultrafast \"$destinationPath1080/$fileName\"";
                // $cmd320 = "$ffmpegPath -y -noautorotate -i \"$withFramePath\" -i \"$moldura320Path\" -filter_complex \"[0:v]scale=320:480,crop=320:448:0:0[scaled];[scaled][1:v]overlay=0:0\" -preset ultrafast \"$destinationPath320/$fileName\"";

                $cmd1080 = "$ffmpegPath -y " .
                    "-i \"$withFramePath\" " .               // vídeo de fundo (principal)
                    "-i \"$molduraPath\" " .                 // vídeo com fundo transparente (com alpha)
                    "-filter_complex \"[1:v]format=yuva420p,fade=t=in:st=0:d=1:alpha=1[moldura_alpha]; " .
                    "[0:v][moldura_alpha]overlay=0:0:format=auto,scale=1080:1920\" " . // overlay com alpha
                    "-t 10 -r 30 -an -c:v libx264 " .
                    "-preset ultrafast " .
                    "\"$destinationPath1080/$fileName\"";

                $cmd320 = "$ffmpegPath -y " .
                    "-i \"$withFramePath\" " .               // input 0: vídeo de fundo
                    "-i \"$moldura320Path\" " .              // input 1: moldura com alpha
                    "-filter_complex \"" .
                    "[0:v]scale=320:480,crop=320:448:0:32[bg]; " .
                    "[1:v]format=yuva420p[moldura_alpha]; " .
                    "[bg][moldura_alpha]overlay=0:0[out]" .
                    "\" -map \"[out]\" -t 10 -r 30 -an -c:v libx264 -preset ultrafast " .
                    "\"$destinationPath320/$fileName\"";


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
                    'media_link_320' => asset("videos/videos_320/$fileName") ?? null,
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

    public function gatheringPiecesAndAddingToTheQueue(Request $request)
    {
        try {
            // dd();
            // $validatedData = $request->validate(
            //     $this->media->rulesMedias(),
            //     $this->media->feedbackMedias()
            // );

            // $user = $request->user();

            // $regionId = $user->fk_region_id;

            // // $resolutionScale1080 = '1080x1920';
            // // $resolutionScale320 = '320x480';

            // $folderTemp = 'temp';

            // if (!File::exists($folderTemp)) {
            //     File::makeDirectory(($folderTemp), 0775, true);
            // }

            // $video = $request->file('video');
            // $fileName = uniqid() . '.' . $video->getClientOriginalExtension();
            // $video->move($folderTemp, $fileName);

            // $fullPath = $folderTemp . DIRECTORY_SEPARATOR . $fileName;
            // $extension = $video->getClientOriginalExtension();
            // $pathTemp = $video->getPathname();

            // if ($validatedData && File::exists($fullPath)) {
            //     ProcessVideoJob::dispatch($fullPath, $pathTemp, $extension, $regionId);

            //     return response()->json([
            //         'success' => true,
            //         'message' => 'Vídeo entrou na fila.'
            //     ]);
            // }

            // $validatedData = $request->validate(
            //     $this->media->rulesMedias(),
            //     $this->media->feedbackMedias()
            // );

            $user = $request->user();
            $regionId = $user->fk_region_id;

            $filename = $request->input('filename');
            $totalChunks = (int) $request->input('totalChunks');

            // $tmpDir = storage_path("app/chunks/$filename");
            // $finalFolder = storage_path("app/public/videos");
            // $finalPath = "$finalFolder/$filename";

            // // Cria a pasta de destino se não existir
            // if (!file_exists($finalFolder)) {
            //     mkdir($finalFolder, 0777, true);
            // }

            // // Junta os chunks
            // $out = fopen($finalPath, 'ab');

            // for ($i = 0; $i < $totalChunks; $i++) {
            //     $chunkPath = "$tmpDir/$i";
            //     if (file_exists($chunkPath)) {
            //         $in = fopen($chunkPath, 'rb');
            //         stream_copy_to_stream($in, $out);
            //         fclose($in);
            //         unlink($chunkPath); // remove o chunk após usar
            //     }
            // }

            // fclose($out);
            // rmdir($tmpDir); // remove o diretório temporário de chunks

            // $tempFolder = 'temp';

            // if (!File::exists($tempFolder)) {
            //     File::makeDirectory($tempFolder, 0775, true);
            // }

            // // move o vídeo finalizado para a pasta temp
            // $fullPath = public_path($tempFolder . DIRECTORY_SEPARATOR . $filename);
            // File::move($finalPath, $fullPath);

            $tmpDir = storage_path("app/chunks/$filename");

            // Novo caminho final: public/temp
            $finalFolder = public_path("temp");
            $finalPath = "$finalFolder/$filename";

            // Cria a pasta de destino se não existir
            if (!file_exists($finalFolder)) {
                mkdir($finalFolder, 0775, true);
            }

            // Junta os chunks
            $out = fopen($finalPath, 'ab');

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = "$tmpDir/$i";
                if (file_exists($chunkPath)) {
                    $in = fopen($chunkPath, 'rb');
                    stream_copy_to_stream($in, $out);
                    fclose($in);
                    unlink($chunkPath); // remove o chunk após usar
                }
            }

            fclose($out);
            rmdir($tmpDir); // remove o diretório temporário de chunks


            // Pega a extensão do arquivo
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            Log::info($finalPath);
            Log::info(file_exists($finalPath));
            dd();
            // Envia para a fila de processamento
            if (file_exists($finalPath)) {
                ProcessVideoJob::dispatch($finalPath, $filename, $extension, $regionId);

                return response()->json([
                    'success' => true,
                    'message' => 'Vídeo montado e entrou na fila.'
                ]);
            }
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function chunks(Request $request)
    {
        try {
            $chunk = $request->file('chunk');
            $index = $request->input('index');
            $filename = $request->input('filename');

            $tmpDir = storage_path("app/chunks/$filename");

            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }

            $chunk->move($tmpDir, $index);

            return response()->json([
                'success' => true,
                'message' => "Chunk $index uploaded"
            ]);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function unir(Request $request)
    {
        try {
            $filename = $request->input('filename');
            $totalChunks = (int) $request->input('totalChunks');
            $tmpDir = storage_path("app/chunks/$filename");

            $path = storage_path("app/public/videos");

            $finalPath = storage_path("app/public/videos/$filename");

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $out = fopen($finalPath, 'ab');

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = "$tmpDir/$i";
                $in = fopen($chunkPath, 'rb');
                stream_copy_to_stream($in, $out);
                fclose($in);
                unlink($chunkPath);
            }

            fclose($out);

            rmdir($tmpDir);

            return response()->json([
                'success' => true,
                'message' => 'Arquivo final salvo com sucesso',
            ]);
        } catch (QueryException $qe) {
            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
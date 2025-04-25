<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;


class UploadMediaController extends Controller
{
    protected $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function upload(Request $request)
    {
        DB::beginTransaction();
        try {

            $user = $request->user();

            $validatedData = $request->validate(
                $this->media->rulesMedias(),
                $this->media->feedbackMedias()
            );

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
                $date = now()->format('d-m-Y_H-i-s');
                $fileName = $infoUsersLocationUf . '_' . $date . '_' . uniqid() . '.' . $extension;

                $destinationPathOriginal = public_path('videos' . DIRECTORY_SEPARATOR . 'original');
                $destinationPath1080 = public_path('videos/videos_1080');
                $destinationPath320 = public_path('videos/videos_320');

                foreach ([$destinationPathOriginal, $destinationPath1080, $destinationPath320] as $path) {
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0775, true);
                    }
                }

                // $originalPath = $destinationPathOriginal . '/' . $fileName;
                $originalPath = $destinationPathOriginal . DIRECTORY_SEPARATOR . $fileName;
                $video->move($destinationPathOriginal, $fileName);

                $ffprobePath = 'C:\\ffmpeg\\ffmpeg-master-latest-win64-gpl-shared\\bin\\ffprobe.exe';
                $cmdGetDuration = "$ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 \"$originalPath\" 2>&1";

                $duration = shell_exec($cmdGetDuration);
                $duration = floatval(trim($duration));

                $duration = floatval(shell_exec($cmdGetDuration));

                if ($duration < 10) {
                    return response()->json([
                        'success' => false,
                        'message' => 'O vídeo precisa ter no mínimo 10 segundos.',
                    ]);
                }

                //cortar para 10 segundos
                $trimmedPath = $destinationPathOriginal . '/trimmed_' . $fileName;
                $cmdTrim = "ffmpeg -y -i \"$originalPath\" -t 10 -c copy \"$trimmedPath\"";
                shell_exec($cmdTrim);


                // Adicionar moldura
                $molduraPath = public_path('Fortal.png');
                $withFramePath = $destinationPathOriginal . '/framed_' . $fileName;
                $cmdFrame = "ffmpeg -y -i \"$trimmedPath\" -i \"$molduraPath\" -filter_complex \"[0:v][1:v] overlay=0:0\" -c:a copy \"$withFramePath\"";
                shell_exec($cmdFrame);

                //gerar resoluções
                $cmd1080 = "ffmpeg -y -i \"$withFramePath\" -vf scale=1080:1920 \"$destinationPath1080/$fileName\"";
                $cmd320 = "ffmpeg -y -i \"$withFramePath\" -vf scale=320:480 \"$destinationPath320/$fileName\"";

                shell_exec($cmd1080);
                shell_exec($cmd320);

                return response()->json([
                    'success' => true,
                    'message' => 'Vídeo processado com sucesso.'
                ]);
            }
        } catch (QueryException $qe) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error DB: ' . $qe->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}

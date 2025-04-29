<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MediaController extends Controller
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

                $infoUsers = User::where('email', $user->email)
                    ->first();

                if ($infoUsers) {
                    if ($infoUsers->fk_region_id == 1) {
                        $infoUsers = 'CE';
                    } elseif ($infoUsers->fk_region_id == 2) {
                        $infoUsers = 'PE';
                        # code...
                    } else {
                        $infoUsers = 'RJ';
                    }
                }
                
                dd($request->file('video')['originalName']);

                $date = now()->format('d-m-Y_H-i-s');
                $path = $infoUsers . '_' . $date;

                dd($path);

                // Salvar vídeo original
                $original = $request->file('video')->store('videos/original');
                dd($original);
                // Caminhos
                $originalPath = storage_path('app/' . $original);
                $cortadoPath = storage_path('app/videos/cortado.mp4');
                $comMolduraPath = storage_path('app/videos/com_moldura.mp4');
                $molduraPath = public_path('moldura.png');

                // Cortar os primeiros 10 segundos
                exec("ffmpeg -i {$originalPath} -t 10 -c copy {$cortadoPath}");

                // Adicionar moldura
                exec("ffmpeg -i {$cortadoPath} -i {$molduraPath} -filter_complex \"overlay=0:0\" {$comMolduraPath}");

                return response()->json([
                    'original' => asset('storage/' . $original),
                    'cortado' => asset('storage/videos/cortado.mp4'),
                    'com_moldura' => asset('storage/videos/com_moldura.mp4'),
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
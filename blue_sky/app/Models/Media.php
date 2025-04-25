<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'media_link',
        'fk_region_id',
    ];

    protected $table = 'medias';
    protected $date = ['deleted_at'];

    public function rulesMedias()
    {
        return [
            'media_link' => 'max:255',
            'fk_region_id' => 'exists:regions,id',
            'video' => 'required|mimetypes:video/mp4,video/webm',
        ];
    }

    public function feedbackMedias()
    {
        return [
            'video.required' => 'O campo vídeo é obrigatório',
            'video.mimetypes' => 'O arquivo necessariamente precisa ser um vídeo mp4.',
            'media_link.max' => 'O campo media_link deve ter até 255 caracteres.',
            'fk_region_id.exists' => 'Nenhum resultado encontrado para a região informada. Por favor, verfique.',
        ];
    }
}
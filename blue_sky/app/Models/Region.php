<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'city',
        'state_uf',
    ];

    protected $table = 'regions';
    protected $date = ['deleted_at'];

    public function rulesRegions()
    {
        return [
            'city' => 'required|max:255',
            'state_uf' => 'required|min:2|max:2',
        ];
    }

    public function feedbackRegions()
    {
        return [
            'city.required' => 'O campo cidade é obrigatório.',
            'city.max' => 'O campo cidade deve conter até 255 caracteres.',
            'state_uf.required' => 'O campo UF é obrigatório.',
            'state_uf.min' => 'O campo UF deve ter no mínimo 2 caracteres.',
            'state_uf.max' => 'O campo UF deve ter até 2 caracteres.',
        ];
    }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'fk_region_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|max:255|unique',
            'password' => 'required|max:30|min:8',
            'fk_region_id' => 'required|exists:regions,id',
        ];
    }

    public function feedback()
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.max' => 'O campo nome deve ter até 255 caracteres.',

            'email.required' => 'O campo email é obrigatório.',
            'email.max' => 'O campo e-mail deve ter até 255 caracteres.',
            'email.unique' => 'E-mail indiponível. Por favor, tente outro e-mail.',

            'password.required' => 'O campo senha é obrigatório.',
            'password.max' => 'O campo senha deve conter até 30 caracteres.',
            'password.min' => 'O campo senha deve ter no mínimo 8 caracteres.',

            'fk_region_id.required' => 'O campo região é obrigatório.',
            'fk_region_id.exists' => 'Nenhum resultado encontrado para a região informada. Por favor, verifique.',
        ];
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'fk_region_id');
    }
}
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {

    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'photo',
        'name',
        'cpfcnpj',
        'email',
        'password',
        'address_postal_code',
        'address_num',
        'address_address',
        'address_city',
        'address_state',
        'role',
    ];

    public function maskName() {
        if (empty($this->name)) {
            return '';
        }

        $nameParts = explode(' ', trim($this->name));

        if (count($nameParts) === 1) {
            return $nameParts[0];
        }

        return $nameParts[0] . ' ' . $nameParts[1];
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function maskCpfCnpj() {

        $value = preg_replace('/\D/', '', $this->cpfcnpj);
        if (strlen($value) === 11) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $value);
        } elseif (strlen($value) === 14) {
            return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $value);
        }

        return $this->cpfcnpj;
    }

    public function favoriteQuestions() {
        return $this->belongsToMany(Question::class, 'favorites');
    }

    public function notebooks() {
        return $this->hasMany(Notebook::class, 'created_by');
    }

    public function questions() {
        return $this->hasMany(NotebookQuestion::class, 'user_id');
    }
}

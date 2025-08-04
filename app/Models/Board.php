<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model {
    
    protected $fillable = [
        'name',
        'state',
        'city',
        'code',
    ];

    public function questions() {
        return $this->hasMany(Question::class);
    }
}

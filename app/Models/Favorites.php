<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorites extends Model {
    
   protected $fillable = [
        'user_id',
        'question_id',
    ];

    public function user() {
        return $this->hasMany(User::class);
    } 

    public function question() {
        return $this->hasMany(Question::class);
    } 
}

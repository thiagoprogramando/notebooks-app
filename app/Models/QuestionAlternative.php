<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAlternative extends Model {

    use SoftDeletes;
    
    protected $fillable = [
        'question_id',
        'label',
        'text',
        'message',
        'is_correct',
    ];

    public function question() {
        return $this->belongsTo(Question::class);
    }
}

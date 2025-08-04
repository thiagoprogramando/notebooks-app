<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model {
    
    protected $table = 'question_comments';

    protected $fillable = [
        'user_id',
        'user_answer_id',
        'question_id',
        'comment_id',
        'comment',
        'comment_answer',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function userAnswer() {
        return $this->belongsTo(User::class, 'user_answer_id');
    }

    public function question() {
        return $this->belongsTo(Question::class);
    }

    public function replies() {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    public function parent() {
        return $this->belongsTo(Comment::class, 'comment_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model {

    use SoftDeletes;
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'tags',
        'order',
        'created_by',
        'content_id',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function content() {
        return $this->belongsTo(Content::class);
    }

    public function questions() {
        return $this->hasMany(Question::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }
}

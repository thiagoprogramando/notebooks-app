<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model {

    use SoftDeletes;

    protected $table = 'contents';
    
    protected $fillable = [
        'title',
        'description',
        'status',
        'tags',
        'icon',
        'cover_image',
        'order',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function topics() {
        return $this->hasMany(Topic::class);
    }

    public function questions() {
        return $this->hasManyThrough(Question::class, Topic::class);
    }
    
}

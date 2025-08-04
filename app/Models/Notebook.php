<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notebook extends Model {

    use SoftDeletes;
    
    protected $fillable = [
        'created_by',
        'title',
        'filters',
        'status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function questions() {
        return $this->hasMany(NotebookQuestion::class);
    }

    public function getTopicsCountAttribute(): int {
        
        $filters = $this->filters;

        if (!is_array($filters)) {
            $filters = json_decode($filters, true);
        }

        if (!isset($filters['topics'])) {
            return 0;
        }

        $topics = json_decode($filters['topics'], true);

        if (!is_array($topics)) {
            return 0;
        }

        return count($topics);
    }

    public function countQuestionsByStatus(int $status, ?int $result = null): int {
        
        $query = $this->questions();
        
        if ($status === 1) {
            $query->whereNotNull('answer_id');
        } elseif ($status === 2) {
            $query->whereNull('answer_id');
        }

        if (!is_null($result)) {
            if ($result === 1) {
                $query->where('answer_result', 1);
            } elseif ($result === 2) {
                $query->where('answer_result', 2); 
            }
        }

        return $query->count();
    }

}

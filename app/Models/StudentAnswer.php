<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke TopicPhase
    public function phase()
    {
        return $this->belongsTo(TopicPhase::class, 'phase_id');
    }

    public function content()
    {
        return $this->belongsTo(PhaseContent::class, 'content_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
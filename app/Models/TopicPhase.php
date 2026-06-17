<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PhaseContent; // <--- INI PENTING

class TopicPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'name',
        'description',
        'order',
        'is_ai_enabled',
        'ai_prompt_setting',
    ];

    protected $casts = [
        'is_ai_enabled' => 'boolean',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    // FUNGSI INI WAJIB ADA
    public function contents(): HasMany
    {
        return $this->hasMany(PhaseContent::class, 'topic_phase_id')->orderBy('order', 'asc');
    }
}
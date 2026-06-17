<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'user_id',
        'title',
        'description',
    ];

    // Relasi ke User (Siapa pembuat topik diskusi ini)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Topic (Diskusi ini untuk materi topik apa)
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    // Relasi ke balasan/komentar (Satu diskusi punya banyak balasan)
    public function replies()
    {
        return $this->hasMany(DiscussionReply::class);
    }
}
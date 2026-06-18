<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
    use HasFactory;

    protected $fillable = [
        'phase_id', // UBAH: Dari topic_id menjadi phase_id
        'user_id',
        'title',
        'description',
    ];

    // Relasi ke User (Siapa pembuat topik diskusi ini)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke TopicPhase (Diskusi ini menempel di fase mana)
    public function phase()
    {
        return $this->belongsTo(TopicPhase::class, 'phase_id');
    }

    // Relasi ke balasan/komentar (Satu diskusi punya banyak balasan)
    public function replies()
    {
        return $this->hasMany(DiscussionReply::class);
    }
}
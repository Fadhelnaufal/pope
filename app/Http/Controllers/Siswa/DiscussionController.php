<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\Phase; // <--- WAJIB DI-IMPORT
use App\Models\Discussion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiscussionController extends Controller
{
    // Tampilkan daftar Forum (Read-Only untuk Siswa)
    public function index(Classroom $classroom, Topic $topic, Phase $phase)
    {
        $discussions = Discussion::with('user')
            ->withCount('replies')
            ->where('phase_id', $phase->id) // <--- UBAH KE phase_id
            ->latest()
            ->get();

        return Inertia::render('Siswa/Discussions/Index', compact('classroom', 'topic', 'phase', 'discussions'));
    }

    // Masuk ke dalam Ruang Obrolan
    public function show(Classroom $classroom, Topic $topic, Phase $phase, Discussion $discussion)
    {
        $discussion->load(['user', 'replies.user']);

        return Inertia::render('Siswa/Discussions/Show', compact('classroom', 'topic', 'phase', 'discussion'));
    }

    // Kirim Balasan/Komentar
    public function storeReply(Request $request, Discussion $discussion)
    {
        $request->validate(['content' => 'required|string']);

        $discussion->replies()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return back();
    }
}
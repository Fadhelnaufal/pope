<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\Phase; // <--- WAJIB DI-IMPORT
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiscussionController extends Controller
{
    /**
     * 1. Menampilkan daftar Thread (Judul & Deskripsi)
     */
    public function index(Classroom $classroom, Topic $topic, Phase $phase)
    {
        $discussions = Discussion::with('user')
            ->withCount('replies') // Hitung jumlah komentar
            ->where('phase_id', $phase->id) // <--- UBAH KE phase_id
            ->latest() // Urutkan dari yang paling baru dibuat
            ->get();

        return Inertia::render('Guru/Discussions/Index', compact('classroom', 'topic', 'phase', 'discussions'));
    }

    /**
     * 2. Menyimpan Thread/Topik Baru (Sudah disesuaikan ke title & description)
     */
    public function store(Request $request, Classroom $classroom, Topic $topic, Phase $phase)
    {
        // Validasi input: mencari title dan description
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Simpan ke database
        Discussion::create([
            'phase_id' => $phase->id, // <--- UBAH KE phase_id
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back();
    }

    /**
     * 3. Menampilkan Halaman Dalam (Ruang Komentar / Chat)
     */
    public function show(Classroom $classroom, Topic $topic, Phase $phase, Discussion $discussion)
    {
        // Tarik data diskusi beserta semua komentarnya
        $discussion->load(['user', 'replies.user']); 

        return Inertia::render('Guru/Discussions/Show', compact('classroom', 'topic', 'phase', 'discussion'));
    }

    /**
     * 4. Simpan Komentar Baru di dalam Thread
     */
    public function storeReply(Request $request, Discussion $discussion)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $discussion->replies()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return back();
    }

    /**
     * 5. Menghapus Thread secara keseluruhan
     */
    public function destroy(Discussion $discussion)
    {
        $discussion->delete();
        return back();
    }

    /**
     * 6. Menghapus Balasan/Komentar secara spesifik
     */
    public function destroyReply(DiscussionReply $reply)
    {
        $reply->delete();
        return back();
    }
}
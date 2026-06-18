<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    /**
     * Menyimpan Balasan (Komentar) Siswa pada Diskusi buatan Guru
     */
    public function reply(Request $request, Discussion $discussion)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Balasan berhasil dikirim!');
    }

    /**
     * Mengupdate Balasan milik Siswa itu sendiri
     */
    public function updateReply(Request $request, DiscussionReply $reply)
    {
        // Pastikan balasan ini benar-benar milik siswa yang sedang login
        if ($reply->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak. Anda tidak bisa mengedit komentar orang lain.');
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $reply->update([
            'content' => $request->content,
        ]);

        return back()->with('success', 'Balasan berhasil diperbarui!');
    }

    /**
     * Menghapus Balasan milik Siswa itu sendiri
     */
    public function destroyReply(Request $request, DiscussionReply $reply)
    {
        // Pastikan balasan ini benar-benar milik siswa yang sedang login
        if ($reply->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak. Anda tidak bisa menghapus komentar orang lain.');
        }

        $reply->delete();

        return back()->with('success', 'Balasan berhasil dihapus.');
    }
}
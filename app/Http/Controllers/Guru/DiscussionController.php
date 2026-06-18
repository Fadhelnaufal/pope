<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\TopicPhase;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function store(Request $request, Classroom $classroom, Topic $topic, TopicPhase $phase)
    {
        $request->validate(['title' => 'required|string|max:255', 'description' => 'required|string']);
        Discussion::create([
            'phase_id' => $phase->id,
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return back()->with('success', 'Topik diskusi berhasil ditambahkan ke fase ini!');
    }

    // FUNGSI BARU: UPDATE TOPIK DISKUSI
    public function update(Request $request, Discussion $discussion)
    {
        $request->validate(['title' => 'required|string|max:255', 'description' => 'required|string']);
        $discussion->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return back()->with('success', 'Topik diskusi berhasil diperbarui!');
    }

    public function storeReply(Request $request, Discussion $discussion)
    {
        $request->validate(['content' => 'required|string']);
        DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);
        return back()->with('success', 'Balasan berhasil dikirim!');
    }

    public function destroy(Discussion $discussion)
    {
        $discussion->delete();
        return back()->with('success', 'Topik diskusi berhasil dihapus.');
    }

    public function destroyReply(DiscussionReply $reply)
    {
        $reply->delete();
        return back()->with('success', 'Balasan berhasil dihapus.');
    }
}
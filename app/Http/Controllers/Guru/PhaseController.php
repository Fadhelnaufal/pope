<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\TopicPhase;
use App\Models\PhaseContent;
use App\Models\Discussion; // <--- TAMBAHAN IMPORT
use App\Services\PhaseService;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    public function __construct(protected PhaseService $phaseService) {}

    // ==========================================
    // MANAJEMEN FASE
    // ==========================================
    public function store(Request $request, Classroom $classroom, Topic $topic)
    {
        if ($classroom->teacher_id !== $request->user()->id) { abort(403, 'Akses ditolak.'); }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $this->phaseService->createPhase($topic, $validated);
        return back()->with('success', 'Fase pembelajaran berhasil ditambahkan!');
    }

    public function show(Request $request, Classroom $classroom, Topic $topic, TopicPhase $phase)
    {
        if ($classroom->teacher_id !== $request->user()->id) { abort(403, 'Akses ditolak.'); }

        $phase->load(['contents' => function($query) {
            $query->orderBy('order', 'asc');
        }]);

        // ==========================================
        // TAMBAHAN: Ambil data Topik Diskusi di Fase ini
        // ==========================================
        $discussions = Discussion::with(['user', 'replies.user'])
            ->where('phase_id', $phase->id)
            ->latest()
            ->get();

        return inertia('Guru/Phases/Show', [
            'classroom' => $classroom,
            'topic' => $topic,
            'phase' => $phase,
            'discussions' => $discussions // <--- KIRIM KE VUE
        ]);
    }

    public function update(Request $request, TopicPhase $phase)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_ai_enabled' => 'boolean',
            'ai_prompt_setting' => 'nullable|string'
        ]);
        
        $this->phaseService->updatePhase($phase, $validated);
        return back();
    }

    public function destroy(TopicPhase $phase)
    {
        $this->phaseService->deletePhase($phase);
        return back();
    }

    // ==========================================
    // MANAJEMEN KONTEN FASE (BUILDER BLOK)
    // ==========================================
    
    /**
     * Menyimpan seluruh blok konten dalam 1 kali Request (Mass Update)
     */
    public function syncContents(Request $request, TopicPhase $phase)
    {
        $request->validate([
            'contents' => 'required|array',
        ]);

        // Kita looping dan update menggunakan relasi Eloquent yang sah dari model TopicPhase
        foreach ($request->contents as $contentData) {
            $phase->contents()
                ->where('id', $contentData['id'])
                ->update([
                    'content_data' => $contentData['content_data']
                ]);
        }

        return back();
    }

    public function storeContent(Request $request, TopicPhase $phase)
    {
        $this->phaseService->createContent($phase, [
            'type' => $request->type,
            'content_data' => $request->content_data
        ]);
        return back();
    }

    public function updateContent(Request $request, PhaseContent $content)
    {
        $this->phaseService->updateContent($content, [
            'content_data' => $request->content_data
        ]);
        return back();
    }

    public function destroyContent(PhaseContent $content)
    {
        $this->phaseService->deleteContent($content);
        return back();
    }
}
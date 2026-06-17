<?php

namespace App\Services;

use App\Models\Topic;
use App\Models\TopicPhase;
use App\Models\PhaseContent;

class PhaseService
{
    public function createPhase(Topic $topic, array $data)
    {
        $data['order'] = $topic->phases()->count() + 1;
        
        // TAMBAHKAN DUA BARIS INI: 
        // Berikan nilai bawaan agar PostgreSQL tidak menolak saat fase pertama kali dibuat
        $data['is_ai_enabled'] = $data['is_ai_enabled'] ?? false;
        $data['description'] = $data['description'] ?? null; 

        return $topic->phases()->create($data);
    }

    public function updatePhase(TopicPhase $phase, array $data)
    {
        $phase->update($data);
        return $phase;
    }

    public function deletePhase(TopicPhase $phase)
    {
        return $phase->delete();
    }

    // Mengelola Konten di dalam Fase (Builder)
    public function createContent(TopicPhase $phase, array $data)
    {
        $data['order'] = $phase->contents()->count() + 1;
        // Default content_data jika tidak ada
        if (!isset($data['content_data'])) {
            $data['content_data'] = [];
        }
        return $phase->contents()->create($data);
    }

    public function updateContent(PhaseContent $content, array $data)
    {
        $content->update($data);
        return $content;
    }

    public function deleteContent(PhaseContent $content)
    {
        return $content->delete();
    }
}
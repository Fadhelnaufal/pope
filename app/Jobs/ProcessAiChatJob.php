<?php

namespace App\Jobs;

use App\Models\AiChatLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use function Laravel\Ai\agent;

class ProcessAiChatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $chatLog;
    public $topicContext;

    public $timeout = 120;
    public $tries = 15;

    public function __construct(AiChatLog $chatLog, string $topicContext = '')
    {
        $this->chatLog = $chatLog;
        $this->topicContext = $topicContext;
    }

    public function handle(): void
    {
        // PENGAMANAN KETAT (Prompt Guardrailing)
        $systemInstruction = "Kamu adalah AI Tutor Kimia untuk siswa SMA yang sangat ramah, suportif, dan interaktif. 
        Saat ini siswa sedang mempelajari materi: {$this->topicContext}.
        
        ATURAN MUTLAK (WAJIB DIPATUHI):
        1. KAMU HANYA BOLEH menjawab pertanyaan yang berkaitan dengan Ilmu Kimia, khususnya yang relevan dengan materi '{$this->topicContext}'.
        2. JIKA siswa bertanya di luar topik Kimia, atau membahas hal lain (seperti game, sejarah, coding, atau sekadar basa-basi yang tidak relevan), KAMU WAJIB MENOLAKNYA dengan ramah dan arahkan mereka kembali ke materi pelajaran.
        3. Gunakan kalimat penolakan seperti: 'Waduh, kalau soal itu di luar kemampuanku sebagai Tutor Kimia nih. 😅 Yuk, kita balik fokus lagi ke materi {$this->topicContext}! Ada yang mau ditanyakan soal itu?'
        4. Jawab dengan singkat, padat (maksimal 2-3 paragraf), dan gunakan gaya bahasa santai seperti teman belajar yang asik (boleh pakai emoji).";

        try {
            $response = agent(
                instructions: $systemInstruction
            )->prompt($this->chatLog->prompt);

            if ($response) {
                $this->chatLog->update([
                    'response' => (string) $response
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            if (str_contains($errorMessage, '429') || str_contains($errorMessage, '503') || str_contains($errorMessage, 'overloaded') || str_contains($errorMessage, 'quota')) {
                Log::warning("Gemini Limit API saat Chatbot berjalan. Menunda antrean 30 detik...");
                $this->release(30); 
                return;
            }

            Log::error('Chatbot AI Exception: ' . $errorMessage);
            throw $e;
        }
    }
}
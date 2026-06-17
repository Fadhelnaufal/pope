<?php

namespace App\Jobs;

use App\Models\StudentAnswer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http; // KITA PAKAI NATIVE HTTP
use Illuminate\Support\Facades\Log;
use Throwable; // TAMBAHAN WAJIB untuk fungsi failed()

class EvaluateStudentAnswerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $answer;
    public $systemPrompt;

    public $timeout = 120;
    
    // 1. KITA NAIKKAN JADI 15 KALI agar lebih tahan banting terhadap limit Gemini
    public $tries = 15; 

    public function __construct(StudentAnswer $answer, ?string $systemPrompt)
    {
        $this->answer = $answer;
        $this->systemPrompt = $systemPrompt;
    }

    public function handle(): void
    {
        $this->answer->load('content');
        
        $question = $this->answer->content->content_data['question'] ?? 'Pertanyaan tidak diketahui';
        $studentAnswerText = $this->answer->answer_data;

        if (empty($studentAnswerText)) return;

        $defaultPrompt = "Kamu adalah guru Kimia yang suportif. Berikan umpan balik singkat atas jawaban siswa ini. Jangan berikan jawaban langsung, tapi berikan petunjuk/clue agar mereka berpikir.";
        $instruction = $this->systemPrompt ?: $defaultPrompt;

        // Gabungkan prompt menjadi satu untuk Native HTTP
        $prompt = "{$instruction}\n\nPERTANYAAN:\n{$question}\n\nJAWABAN SISWA:\n{$studentAnswerText}\n\nBerikan evaluasi atau feedbackmu:";

        try {
            $apiKey = env('GEMINI_API_KEY');
            
            // SOLUSI FINAL: Kita tembak ke model 'gemini-pro' yang paling stabil dan 100% support semua API Key
            $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                $reply = $response->json('candidates.0.content.parts.0.text');
                
                if ($reply) {
                    $this->answer->update([
                        'ai_feedback' => $reply
                    ]);
                }
            } else {
                // Ambil pesan error dari Google untuk trigger retry
                $pesanError = $response->json('error.message') ?? $response->body();
                throw new \Exception("Google API Error: " . $pesanError);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Trik Pro: Jika error karena limit dari Google (429 atau 503)
            if (str_contains($errorMessage, '429') || str_contains($errorMessage, '503') || str_contains($errorMessage, 'overloaded') || str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'high demand')) {
                Log::warning("Gemini Limit API tercapai. Menunda antrean selama 60 detik... (Percobaan ke-" . $this->attempts() . ")");
                
                // Lempar kembali ke antrean, suruh tunggu 60 detik baru dieksekusi lagi
                $this->release(60); 
                return;
            }

            // Jika error lain (misal kodingan salah), baru lempar sebagai error beneran
            Log::error('Native HTTP Gemini Exception: ' . $errorMessage);
            throw $e;
        }
    }

    /**
     * 2. TAMBAHKAN METHOD failed() INI
     * Jika sudah diulang 15 kali tapi tetap limit/gagal, selamatkan UI Frontend dengan pesan ini.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("Job AI Evaluasi mati total setelah {$this->tries} kali mencoba: " . $exception->getMessage());

        $this->answer->update([
            'ai_feedback' => '⚠️ **Sistem AI Sedang Sangat Sibuk** ⚠️<br><br>Maaf, server AI saat ini sedang memproses terlalu banyak antrean. Mohon klik tombol **"Cek Hasil AI"** beberapa saat lagi, atau diskusikan jawaban ini secara langsung dengan Guru Anda.'
        ]);
    }
}
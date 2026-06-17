<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\TopicPhase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    public function chat(Request $request, TopicPhase $phase)
    {
        $request->validate(['message' => 'required|string']);

        // 1. Ambil seluruh konten materi sebagai konteks
        $materi = $phase->contents->pluck('content_data')->toJson();

        // 2. Siapkan Prompt
        $prompt = "Kamu adalah Tutor AI ahli kimia. Gunakan materi berikut sebagai referensi utama:\n{$materi}\n\nPertanyaan siswa: {$request->message}";

        $apiKey = env('GEMINI_API_KEY');

        try {
            // 3. Tembak Gemini (KITA UBAH KE gemini-pro AGAR SINKRON DENGAN JOB)
            $response = Http::timeout(60)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
                'contents' => [['parts' => [['text' => $prompt]]]]
            ]);

            if ($response->successful()) {
                return response()->json([
                    'reply' => $response->json('candidates.0.content.parts.0.text')
                ]);
            }

            // Jika Google membalas tapi error (misal 503 Overloaded atau 404 Not Found)
            Log::error('AI Chat Error (Response): ' . $response->body());
            return response()->json([
                'reply' => 'Maaf, server AI sedang sibuk atau mengalami gangguan. Coba tanya lagi dalam beberapa detik ya!'
            ], 503);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Menangkap khusus error Timeout (cURL error 28)
            Log::error('AI Chat Timeout: ' . $e->getMessage());
            return response()->json([
                'reply' => 'Waktu tunggu habis. AI sedang berpikir terlalu lama. Silakan coba tanyakan lagi.'
            ], 504);
            
        } catch (\Exception $e) {
            // Menangkap error lainnya
            Log::error('AI Chat Exception: ' . $e->getMessage());
            return response()->json([
                'reply' => 'Waduh, ada sedikit gangguan sistem. Hubungi guru ya.'
            ], 500);
        }
    }
}
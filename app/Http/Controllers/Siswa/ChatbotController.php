<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\TopicPhase;
use App\Jobs\ProcessAiChatJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function index()
    {
        try {
            if (!auth()->check()) return response()->json(['error' => 'Unauthenticated'], 401);

            $logs = AiChatLog::where('user_id', auth()->id())->orderBy('created_at', 'asc')->get();
            return response()->json($logs);

        } catch (\Exception $e) {
            Log::error('Chatbot Index Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mengambil data: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validasi diwajibkan mengirim phase_id
            $request->validate([
                'prompt' => 'required|string',
                'topic_context' => 'nullable|string',
                'phase_id' => 'required|integer|exists:topic_phases,id'
            ]);

            if (!auth()->check()) {
                return response()->json(['error' => 'Sesi Anda telah habis. Silakan refresh halaman.'], 401);
            }

            // ========================================================
            // KILL-SWITCH (POIN 9): CEK APAKAH GURU MENGAKTIFKAN AI 
            // ========================================================
            $phase = TopicPhase::find($request->phase_id);
            if (!$phase || !$phase->is_ai_enabled) {
                return response()->json(['error' => 'Fitur AI telah dinonaktifkan oleh Guru pada sesi ini. Chat tidak terkirim.'], 403);
            }

            // Simpan log pertanyaan siswa
            $chatLog = AiChatLog::create([
                'user_id' => auth()->id(),
                'prompt' => $request->prompt,
                'response' => null, 
            ]);

            // Gabungkan konteks materi dengan Prompt Khusus dari Guru (Jika ada)
            $context = $request->topic_context ?? 'Materi Kimia';
            if (!empty($phase->ai_prompt_setting)) {
                $context .= " | Instruksi Khusus Evaluator: " . $phase->ai_prompt_setting;
            }

            ProcessAiChatJob::dispatch($chatLog, $context);

            return response()->json(['status' => 'success', 'log_id' => $chatLog->id]);

        } catch (\Exception $e) {
            Log::error('Chatbot Store Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal terhubung ke server AI. Coba lagi nanti.'], 500);
        }
    }
}
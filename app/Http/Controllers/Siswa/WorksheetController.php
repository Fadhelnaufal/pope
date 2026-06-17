<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\TopicPhase;
use App\Models\StudentAnswer;
use App\Models\PhaseContent;
use App\Jobs\EvaluateStudentAnswerJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorksheetController extends Controller
{
    /**
     * Menampilkan Halaman Belajar (Lembar Kerja) Fase POE kepada Siswa
     */
    public function show(Request $request, Classroom $classroom, Topic $topic, TopicPhase $phase)
    {
        if (!$request->user()->joinedClasses()->where('class_id', $classroom->id)->exists()) {
            abort(403, 'Akses ditolak. Anda tidak terdaftar di kelas ini.');
        }

        if (!$topic->is_published) {
            abort(403, 'Materi ini masih berstatus DRAFT dan belum dipublikasikan oleh Guru.');
        }

        $phase->load(['contents' => function($query) {
            $query->orderBy('order', 'asc');
        }]);

        // Ambil semua data jawaban beserta evaluasi AI-nya
        $studentData = StudentAnswer::where('user_id', $request->user()->id)
            ->where('phase_id', $phase->id)
            ->get();

        // Pisahkan menjadi array yang mudah dibaca Vue
        $studentAnswers = $studentData->pluck('answer_data', 'content_id')->toArray();
        $aiFeedbacks = $studentData->pluck('ai_feedback', 'content_id')->toArray();
        $studentScores = $studentData->pluck('score', 'content_id')->toArray();
        $studentIsCorrect = $studentData->pluck('is_correct', 'content_id')->toArray();
        
        // AMBIL DATA FILE UPLOAD & UBAH JADI URL AGAR BISA DILIHAT DI VUE
        $studentFiles = $studentData->pluck('file_path', 'content_id')->map(function($path) {
            return $path ? asset('storage/' . $path) : null;
        })->toArray();

        // =========================================================
        // CEK APAKAH FASE INI SUDAH DI-SUBMIT (DIKUNCI) OLEH SISWA
        // =========================================================
        $isPhaseSubmitted = $studentData->where('is_submitted', true)->isNotEmpty();

        return Inertia::render('Siswa/Worksheet/Show', [
            'classroom' => $classroom,
            'topic' => $topic,
            'phase' => $phase,
            'studentAnswers' => (object) $studentAnswers,
            'aiFeedbacks' => (object) $aiFeedbacks, 
            'studentScores' => (object) $studentScores,
            'studentIsCorrect' => (object) $studentIsCorrect,
            'studentFiles' => (object) $studentFiles, // KIRIM DATA FILE KE VUE
            'isPhaseSubmitted' => $isPhaseSubmitted, 
        ]);
    }

    /**
     * Menyimpan Jawaban Siswa (Termasuk Auto-Correct Pilihan Ganda, Upload File & Submit Final)
     */
    public function storeAnswer(Request $request, TopicPhase $phase)
    {
        // =========================================================
        // 1. CEK JIKA INI ADALAH REQUEST FINAL SUBMIT (KUNCI PERMANEN)
        // =========================================================
        if ($request->has('is_final_submit') && $request->is_final_submit == true) {
            
            $firstContent = $phase->contents()->first();
            if ($firstContent) {
                StudentAnswer::firstOrCreate(
                    ['user_id' => $request->user()->id, 'content_id' => $firstContent->id],
                    ['phase_id' => $phase->id]
                );
            }

            // Kunci SEMUA jawaban siswa di fase ini agar tidak bisa diubah lagi
            StudentAnswer::where('user_id', $request->user()->id)
                ->where('phase_id', $phase->id)
                ->update(['is_submitted' => true]);

            return back()->with('success', 'Materi berhasil dikumpulkan dan dikunci!');
        }

        // =========================================================
        // 2. LOGIKA SIMPAN JAWABAN & FILE
        // =========================================================
        $validated = $request->validate([
            'content_id' => 'required|integer|exists:phase_contents,id',
            'answer_text' => 'nullable', // Boleh string atau array
            'answer_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // Diubah jadi 10240 = 10MB
        ]);

        $userId = $request->user()->id;
        $answerText = $request->input('answer_text');
        
        // Data dasar yang akan diupdate ke Database
        $updateData = [
            'phase_id' => $phase->id, 
            'ai_feedback' => null       
        ];

        // Jika siswa mengirim jawaban teks/pilihan, masukkan ke kolom answer_data
        if ($request->has('answer_text')) {
            $updateData['answer_data'] = is_array($answerText) ? json_encode($answerText) : $answerText;
        }

        // Jika siswa mengupload file, simpan file-nya, dan masukkan path-nya ke kolom file_path
        if ($request->hasFile('answer_file')) {
            $path = $request->file('answer_file')->store('student_uploads', 'public');
            $updateData['file_path'] = $path; 
        }

        // ==========================================
        // LOGIKA AUTO-CORRECT (NILAI PILIHAN GANDA)
        // ==========================================
        $content = PhaseContent::findOrFail($validated['content_id']);
        
        if (in_array($content->type, ['eval_mcq', 'eval_cmcq'])) {
            $contentData = $content->content_data;
            $isCorrect = false;
            $score = 0;

            if ($content->type === 'eval_mcq') {
                $correctAnswer = $contentData['correct_answer'] ?? '';
                $isCorrect = ($answerText === $correctAnswer);
                $score = $isCorrect ? 100 : 0;
            } 
            elseif ($content->type === 'eval_cmcq') {
                $correctAnswers = $contentData['correct_answers'] ?? [];
                $studentAnswersArray = is_string($answerText) ? json_decode($answerText, true) : $answerText;
                
                if (!is_array($studentAnswersArray)) $studentAnswersArray = [];
                
                $diff1 = array_diff($correctAnswers, $studentAnswersArray);
                $diff2 = array_diff($studentAnswersArray, $correctAnswers);
                
                $isCorrect = empty($diff1) && empty($diff2) && count($studentAnswersArray) > 0;
                $score = $isCorrect ? 100 : 0;
            }

            $updateData['is_correct'] = $isCorrect;
            $updateData['score'] = $score;
        }

        // ==========================================
        // SIMPAN KE DATABASE
        // ==========================================
        $answer = StudentAnswer::updateOrCreate(
            ['user_id' => $userId, 'content_id' => $validated['content_id']],
            $updateData
        );

        // --- PENTING: Load relasi sebelum Dispatch Job ---
        $answer->load('content');

        // Picu AI Evaluasi HANYA JIKA ada jawaban teks dan tipe soalnya Esai
        if ($phase->is_ai_enabled && in_array($answer->content->type, ['eval_essay', 'eval_short']) && !empty($answer->answer_data)) {
             EvaluateStudentAnswerJob::dispatch($answer, $phase->ai_prompt_setting);
        }

        return back()->with('success', 'Jawaban dan lampiran berhasil disimpan!');
    }
}
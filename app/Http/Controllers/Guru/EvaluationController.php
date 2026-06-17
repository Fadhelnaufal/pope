<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Topic;
use App\Models\TopicPhase;
use App\Models\User;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EvaluationController extends Controller
{
    /**
     * Menampilkan daftar siswa dan status pengerjaan mereka
     */
    public function index(Request $request, Classroom $classroom, Topic $topic, TopicPhase $phase)
    {
        // Pastikan Guru yang mengakses adalah pemilik kelas
        if ($classroom->teacher_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil semua siswa yang tergabung di kelas ini
        $students = User::whereHas('joinedClasses', function ($query) use ($classroom) {
            $query->where('class_id', $classroom->id);
        })->get()->map(function ($student) use ($phase) {
            
            // Ambil jawaban siswa untuk fase ini
            $answers = StudentAnswer::where('user_id', $student->id)
                ->where('phase_id', $phase->id)
                ->get();

            // Cek apakah siswa sudah menekan "Selesai & Kumpulkan"
            $isSubmitted = $answers->where('is_submitted', true)->isNotEmpty();
            
            // Hitung rata-rata nilai (Hanya dari soal yang sudah memiliki nilai/score)
            $gradedAnswers = $answers->whereNotNull('score');
            $averageScore = $gradedAnswers->count() > 0 ? round($gradedAnswers->avg('score')) : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'is_submitted' => $isSubmitted,
                'submitted_at' => $isSubmitted ? $answers->where('is_submitted', true)->first()->updated_at->format('d M Y, H:i') : null,
                'score' => $averageScore,
            ];
        });

        return Inertia::render('Guru/Evaluations/Index', [
            'classroom' => $classroom,
            'topic' => $topic,
            'phase' => $phase,
            'students' => $students
        ]);
    }

    /**
     * Menampilkan detail jawaban 1 siswa untuk dinilai manual oleh Guru
     */
    public function show(Request $request, Classroom $classroom, Topic $topic, TopicPhase $phase, User $student)
    {
        if ($classroom->teacher_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }

        $phase->load(['contents' => function($query) {
            $query->orderBy('order', 'asc');
        }]);

        $answers = StudentAnswer::where('user_id', $student->id)
            ->where('phase_id', $phase->id)
            ->get();

        $studentAnswers = $answers->pluck('answer_data', 'content_id')->toArray();
        $studentScores = $answers->pluck('score', 'content_id')->toArray();
        $aiFeedbacks = $answers->pluck('ai_feedback', 'content_id')->toArray();

        // ==============================================================
        // TAMBAHAN: Ambil Path File dan ubah jadi URL Publik yang bisa diklik
        // ==============================================================
        $studentFiles = $answers->pluck('file_path', 'content_id')->map(function($path) {
            return $path ? asset('storage/' . $path) : null;
        })->toArray();

        return Inertia::render('Guru/Evaluations/Show', [
            'classroom' => $classroom,
            'topic' => $topic,
            'phase' => $phase,
            'student' => $student->only('id', 'name', 'email'),
            'studentAnswers' => (object) $studentAnswers,
            'studentScores' => (object) $studentScores,
            'aiFeedbacks' => (object) $aiFeedbacks,
            'studentFiles' => (object) $studentFiles, // KIRIM DATA FILE KE VUE GURU
        ]);
    }

    /**
     * Menyimpan nilai manual yang diinputkan Guru (misal untuk soal Esai)
     */
    public function updateScore(Request $request, TopicPhase $phase, User $student)
    {
        $validated = $request->validate([
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100'
        ]);

        foreach ($validated['scores'] as $contentId => $score) {
            if ($score !== null) {
                StudentAnswer::where('user_id', $student->id)
                    ->where('phase_id', $phase->id)
                    ->where('content_id', $contentId)
                    ->update(['score' => $score]);
            }
        }

        return back()->with('success', 'Nilai berhasil disimpan!');
    }
}
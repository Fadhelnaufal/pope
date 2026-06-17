<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phase_id')->constrained('topic_phases')->cascadeOnDelete();
            $table->foreignId('content_id')->constrained('phase_contents')->cascadeOnDelete();
            
            // Menggunakan longText/JSON untuk menampung teks, array (checkbox), atau path gambar
            $table->longText('answer_data')->nullable(); 
            
            // ==========================================
            // KOLOM BARU: UNTUK REKAP NILAI (AUTO-CORRECT)
            // ==========================================
            $table->boolean('is_correct')->nullable(); 
            $table->integer('score')->default(0);      
            
            // Ini tempat AI memberikan nilai/feedback atas jawaban di atas
            $table->text('ai_feedback')->nullable(); 
            
            $table->timestamps();

            // Constraint: Satu siswa hanya boleh punya 1 record jawaban per 1 blok pertanyaan
            $table->unique(['user_id', 'content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
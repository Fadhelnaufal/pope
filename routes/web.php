<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\TopicController;
use App\Http\Controllers\Guru\PhaseController;
use App\Http\Controllers\Guru\EvaluationController; 
use App\Http\Controllers\Guru\DiscussionController as GuruDiscussionController;
use App\Http\Controllers\Siswa\ClassroomController as SiswaClassroomController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\WorksheetController as SiswaWorksheetController;
use App\Http\Controllers\Siswa\ChatbotController;
use App\Http\Controllers\Siswa\DiscussionController as SiswaDiscussionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store']);

// =================================================================
// PENGATUR LALU LINTAS DASHBOARD
// =================================================================
Route::get('dashboard', function (Request $request) {
    $user = $request->user();
    if ($user->hasRole(['ADMIN', 'admin', 'Admin'])) return redirect()->route('admin.dashboard');
    if ($user->hasRole(['GURU', 'guru', 'Guru'])) return redirect()->route('guru.dashboard');
    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// =================================================================
// AREA KHUSUS ADMIN
// =================================================================
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/upgrade', [UserController::class, 'upgrade'])->name('users.upgrade');
});

// =================================================================
// AREA KHUSUS GURU
// =================================================================
Route::middleware(['auth', 'role:GURU'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');
    Route::resource('classes', GuruDashboardController::class)->only(['index', 'store', 'update', 'destroy', 'show']);

    Route::post('classes/{classroom}/topics/{topic}/toggle-publish', [TopicController::class, 'togglePublish'])->name('classes.topics.toggle-publish');
    Route::resource('classes.topics', TopicController::class)->parameters(['classes' => 'classroom'])->only(['store', 'update', 'destroy', 'show']);

    Route::post('classes/{classroom}/topics/{topic}/phases', [PhaseController::class, 'store'])->name('phases.store');
    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}', [PhaseController::class, 'show'])->name('phases.show');
    Route::put('phases/{phase}', [PhaseController::class, 'update'])->name('phases.update');
    Route::delete('phases/{phase}', [PhaseController::class, 'destroy'])->name('phases.destroy');

    Route::put('phases/{phase}/contents-sync', [PhaseController::class, 'syncContents'])->name('contents.sync'); 
    Route::post('phases/{phase}/contents', [PhaseController::class, 'storeContent'])->name('contents.store');
    Route::put('contents/{content}', [PhaseController::class, 'updateContent'])->name('contents.update');
    Route::delete('contents/{content}', [PhaseController::class, 'destroyContent'])->name('contents.destroy');

    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}/evaluations', [EvaluationController::class, 'index'])->name('phases.evaluations.index');
    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}/evaluations/{student}', [EvaluationController::class, 'show'])->name('phases.evaluations.show');
    Route::put('phases/{phase}/evaluations/{student}', [EvaluationController::class, 'updateScore'])->name('phases.evaluations.update');

    // ==========================================
    // FORUM DISKUSI FASE (GURU)
    // ==========================================
    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}/discussions', [GuruDiscussionController::class, 'index'])->name('classes.topics.phases.discussions.index');
    Route::post('classes/{classroom}/topics/{topic}/phases/{phase}/discussions', [GuruDiscussionController::class, 'store'])->name('classes.topics.phases.discussions.store');
    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}/discussions/{discussion}', [GuruDiscussionController::class, 'show'])->name('classes.topics.phases.discussions.show');
    Route::delete('discussions/{discussion}', [GuruDiscussionController::class, 'destroy'])->name('classes.topics.phases.discussions.destroy');
    
    Route::post('discussions/{discussion}/replies', [GuruDiscussionController::class, 'storeReply'])->name('discussions.replies.store');
    Route::delete('replies/{reply}', [GuruDiscussionController::class, 'destroyReply'])->name('discussions.replies.destroy');
});

// =================================================================
// AREA KHUSUS SISWA
// =================================================================
Route::middleware(['auth', 'role:SISWA'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    Route::get('/classes', [SiswaClassroomController::class, 'index'])->name('classes.index');
    Route::post('/classes/join', [SiswaClassroomController::class, 'join'])->name('classes.join');
    Route::get('/classes/{classroom}', [SiswaClassroomController::class, 'show'])->name('classes.show');

    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}', [SiswaWorksheetController::class, 'show'])->name('worksheet.show');
    Route::post('phases/{phase}/answers', [SiswaWorksheetController::class, 'storeAnswer'])->name('answers.store');
        
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot', [ChatbotController::class, 'store'])->name('chatbot.store');

    // ==========================================
    // FORUM DISKUSI FASE (SISWA)
    // ==========================================
    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}/discussions', [SiswaDiscussionController::class, 'index'])->name('classes.topics.phases.discussions.index');
    Route::get('classes/{classroom}/topics/{topic}/phases/{phase}/discussions/{discussion}', [SiswaDiscussionController::class, 'show'])->name('classes.topics.phases.discussions.show');
    Route::post('discussions/{discussion}/replies', [SiswaDiscussionController::class, 'storeReply'])->name('discussions.replies.store');
});

require __DIR__.'/settings.php';
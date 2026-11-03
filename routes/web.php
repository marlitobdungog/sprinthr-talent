<?php

use App\Http\Controllers\CandidateProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataRequestController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('profile', [CandidateProfileController::class, 'edit'])->name('candidate.profile.edit');
    Route::patch('profile', [CandidateProfileController::class, 'update'])->name('candidate.profile.update');

    Route::get('profile/skills', [SkillController::class, 'index'])->name('candidate.skills.index');
    Route::post('profile/skills', [SkillController::class, 'store'])->name('candidate.skills.store');
    Route::delete('profile/skills/{skill}', [SkillController::class, 'destroy'])->whereNumber('skill')->name('candidate.skills.destroy');

    Route::get('profile/experience', [ExperienceController::class, 'index'])->name('candidate.experience.index');
    Route::post('profile/experience', [ExperienceController::class, 'storeExperience'])->name('candidate.experience.store');
    Route::patch('profile/experience/{experience}', [ExperienceController::class, 'updateExperience'])->whereNumber('experience')->name('candidate.experience.update');
    Route::delete('profile/experience/{experience}', [ExperienceController::class, 'destroyExperience'])->whereNumber('experience')->name('candidate.experience.destroy');
    Route::post('profile/education', [ExperienceController::class, 'storeEducation'])->name('candidate.education.store');
    Route::patch('profile/education/{education}', [ExperienceController::class, 'updateEducation'])->whereNumber('education')->name('candidate.education.update');
    Route::delete('profile/education/{education}', [ExperienceController::class, 'destroyEducation'])->whereNumber('education')->name('candidate.education.destroy');

    Route::get('resume', [ResumeController::class, 'index'])->name('candidate.resume.index');
    Route::post('resume', [ResumeController::class, 'store'])->middleware('throttle:6,1')->name('candidate.resume.store');
    Route::get('resume/download', [ResumeController::class, 'download'])->name('candidate.resume.download');
    Route::delete('resume', [ResumeController::class, 'destroy'])->name('candidate.resume.destroy');

    Route::get('privacy', [PrivacyController::class, 'index'])->name('candidate.privacy.index');
    Route::patch('privacy', [PrivacyController::class, 'update'])->name('candidate.privacy.update');

    Route::get('invitations', [InvitationController::class, 'index'])->name('candidate.invitations.index');
    Route::patch('invitations/{invitation}', [InvitationController::class, 'respond'])->name('candidate.invitations.respond');

    Route::get('settings/data', [DataRequestController::class, 'index'])->name('candidate.data.index');
    Route::get('settings/data/export', [DataRequestController::class, 'export'])->middleware('throttle:5,1')->name('candidate.data.export');
    Route::post('settings/data/deletion-request', [DataRequestController::class, 'requestDeletion'])->middleware('throttle:2,60')->name('candidate.data.deletion-request');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();
        $candidate->load(['user', 'skills', 'experiences', 'educations', 'resume']);

        return Inertia::render('dashboard', [
            'candidate' => [
                'name' => $candidate->user->name,
                'headline' => $candidate->headline,
                'coarse_location' => $candidate->coarse_location,
                'availability' => $candidate->availability,
                'discoverable' => $candidate->discoverable,
                'completeness' => $candidate->completeness(),
                'skills' => $candidate->skills->take(5)->map(fn ($skill) => ['id' => $skill->id, 'name' => $skill->name]),
                'experience_count' => $candidate->experiences->count(),
                'education_count' => $candidate->educations->count(),
                'resume' => $candidate->resume ? [
                    'filename' => $candidate->resume->original_filename,
                    'size_bytes' => $candidate->resume->size_bytes,
                    'parse_status' => $candidate->resume->parse_status,
                    'uploaded_at' => $candidate->resume->uploaded_at->toDateString(),
                ] : null,
            ],
            'invitations' => $candidate->invitations()
                ->where('status', 'pending')
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->latest()
                ->limit(3)
                ->get(['id', 'employer_name', 'role_title', 'expires_at']),
        ]);
    }
}

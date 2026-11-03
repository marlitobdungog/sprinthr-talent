<?php

namespace App\Http\Controllers;

use App\Services\CandidateConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DataRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();

        return Inertia::render('settings/data', [
            'deletionRequest' => $candidate->dataRequests()->where('request_type', 'deletion')->latest()->first(),
        ]);
    }

    public function export(Request $request): mixed
    {
        $candidate = $request->user()->candidateProfile()->load([
            'user:id,name,email', 'skills', 'experiences', 'educations', 'resume', 'consentEvents', 'invitations',
        ]);
        $resume = $candidate->resume;
        $export = [
            'exported_at' => now()->toIso8601String(),
            'account' => ['name' => $candidate->user->name, 'email' => $candidate->user->email],
            'profile' => $candidate->only(['headline', 'coarse_location', 'work_preferences', 'availability', 'summary', 'discoverable']),
            'skills' => $candidate->skills,
            'experience' => $candidate->experiences,
            'education' => $candidate->educations,
            'resume' => $resume ? $resume->only(['original_filename', 'mime_type', 'size_bytes', 'version', 'uploaded_at']) : null,
            'consent_history' => $candidate->consentEvents,
            'invitations' => $candidate->invitations,
        ];

        return response()->streamDownload(
            fn () => print(json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)),
            'sprint-hr-talent-data.json',
            ['Content-Type' => 'application/json; charset=UTF-8', 'X-Content-Type-Options' => 'nosniff'],
        );
    }

    public function requestDeletion(Request $request, CandidateConsentService $consent): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();

        if ($candidate->dataRequests()->where('request_type', 'deletion')->where('status', 'pending')->exists()) {
            return back()->with('success', 'Your deletion request is already queued.');
        }

        $candidate->dataRequests()->create(['request_type' => 'deletion', 'requested_at' => now()]);

        $consent->setDiscoverable($candidate, false);

        return back()->with('success', 'Your deletion request is queued. Discovery has been turned off while it is reviewed.');
    }
}

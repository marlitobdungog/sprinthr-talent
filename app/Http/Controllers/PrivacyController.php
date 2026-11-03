<?php

namespace App\Http\Controllers;

use App\Services\CandidateConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();

        return Inertia::render('privacy/index', [
            'discoverable' => $candidate->discoverable,
            'can_opt_in' => app(CandidateConsentService::class)->canDiscover($candidate),
            'preview' => [
                'headline' => $candidate->headline ?: 'Your professional headline',
                'location' => $candidate->coarse_location ?: 'Your region',
                'skills' => $candidate->skills()->orderBy('name')->limit(6)->pluck('name'),
                'work_history' => $this->workHistory($candidate->experiences()->count()),
            ],
            'history' => $candidate->consentEvents()
                ->where('purpose', 'discovery')
                ->latest('created_at')
                ->limit(10)
                ->get(['action', 'policy_version', 'created_at']),
            'policy_version' => config('talent.consent_policy_version'),
        ]);
    }

    public function update(Request $request, CandidateConsentService $consent): RedirectResponse
    {
        $data = $request->validate(['discoverable' => ['required', 'boolean']]);
        $candidate = $request->user()->candidateProfile();
        $discoverable = (bool) $data['discoverable'];

        $consent->setDiscoverable($candidate, $discoverable);

        return back()->with('success', $discoverable ? 'You are opted in to employer discovery.' : 'Your profile is hidden from new discovery.');
    }

    private function workHistory(int $count): string
    {
        return match (true) {
            $count === 0 => 'No work history added',
            $count === 1 => '1 role listed',
            $count < 4 => '2–3 roles listed',
            default => '4+ roles listed',
        };
    }
}

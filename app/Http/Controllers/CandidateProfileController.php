<?php

namespace App\Http\Controllers;

use App\Services\CandidateConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CandidateProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();
        $candidate->load(['user', 'skills', 'experiences', 'educations']);

        return Inertia::render('profile/edit', [
            'candidate' => [
                'name' => $candidate->user->name,
                'headline' => $candidate->headline,
                'coarse_location' => $candidate->coarse_location,
                'work_preferences' => $candidate->work_preferences ?? [],
                'availability' => $candidate->availability,
                'summary' => $candidate->summary,
            ],
            'completeness' => $candidate->completeness(),
        ]);
    }

    public function update(Request $request, CandidateConsentService $consent): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:140'],
            'coarse_location' => ['nullable', 'string', 'max:120'],
            'work_preferences' => ['nullable', 'array', 'max:3'],
            'work_preferences.*' => [Rule::in(['remote', 'hybrid', 'onsite'])],
            'availability' => ['nullable', Rule::in(['open_now', 'one_month', 'exploring', 'not_looking'])],
            'summary' => ['nullable', 'string', 'max:2400'],
        ]);

        $request->user()->update(['name' => $data['name']]);
        unset($data['name']);
        $candidate->fill($data);
        $candidate->profile_version++;
        $candidate->save();
        $withdrawn = $consent->withdrawIfIncomplete($candidate);

        return back()->with('success', $withdrawn
            ? 'Profile saved. Discovery was turned off because your profile no longer has the minimum details.'
            : 'Profile saved.');
    }
}

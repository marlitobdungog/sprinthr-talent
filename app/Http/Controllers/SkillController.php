<?php

namespace App\Http\Controllers;

use App\Models\CandidateSkill;
use App\Services\CandidateConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();

        return Inertia::render('profile/skills', [
            'skills' => $candidate->skills()->orderBy('name')->get(['id', 'name', 'experience_years']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);
        $normalized = Str::lower(trim($data['name']));

        if ($candidate->skills()->where('normalized_name', $normalized)->exists()) {
            return back()->withErrors(['name' => 'That skill is already in your profile.']);
        }

        $candidate->skills()->create([
            'name' => trim($data['name']),
            'normalized_name' => $normalized,
            'experience_years' => $data['experience_years'] ?? null,
        ]);
        $candidate->increment('profile_version');

        return back()->with('success', 'Skill added.');
    }

    public function destroy(Request $request, CandidateSkill $skill, CandidateConsentService $consent): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        abort_unless($skill->candidate_id === $candidate->id, 404);
        $skill->delete();
        $candidate->increment('profile_version');
        $withdrawn = $consent->withdrawIfIncomplete($candidate);

        return back()->with('success', $withdrawn
            ? 'Skill removed. Discovery was turned off because your profile no longer has the minimum details.'
            : 'Skill removed.');
    }
}

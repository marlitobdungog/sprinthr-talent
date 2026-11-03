<?php

namespace App\Http\Controllers;

use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExperienceController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();

        return Inertia::render('profile/experience', [
            'experiences' => $candidate->experiences()->get(),
            'educations' => $candidate->educations()->get(),
        ]);
    }

    public function storeExperience(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'company' => ['required', 'string', 'max:140'],
            'started_on' => ['nullable', 'date_format:Y-m-d'],
            'ended_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:started_on'],
            'current_role' => ['boolean'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        if ($data['current_role'] ?? false) {
            $data['ended_on'] = null;
        }

        $candidate = $request->user()->candidateProfile();
        $candidate->experiences()->create($data);
        $candidate->increment('profile_version');

        return back()->with('success', 'Experience saved.');
    }

    public function updateExperience(Request $request, CandidateExperience $experience): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        abort_unless($experience->candidate_id === $candidate->id, 404);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'company' => ['required', 'string', 'max:140'],
            'started_on' => ['nullable', 'date_format:Y-m-d'],
            'ended_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:started_on'],
            'current_role' => ['boolean'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        if ($data['current_role'] ?? false) {
            $data['ended_on'] = null;
        }

        $experience->update($data);
        $candidate->increment('profile_version');

        return back()->with('success', 'Experience updated.');
    }

    public function destroyExperience(Request $request, CandidateExperience $experience): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        abort_unless($experience->candidate_id === $candidate->id, 404);
        $experience->delete();
        $candidate->increment('profile_version');

        return back()->with('success', 'Experience removed.');
    }

    public function storeEducation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'institution' => ['required', 'string', 'max:160'],
            'qualification' => ['nullable', 'string', 'max:160'],
            'started_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'ended_year' => ['nullable', 'integer', 'min:1950', 'max:2100', 'gte:started_year'],
        ]);

        $candidate = $request->user()->candidateProfile();
        $candidate->educations()->create($data);
        $candidate->increment('profile_version');

        return back()->with('success', 'Education saved.');
    }

    public function updateEducation(Request $request, CandidateEducation $education): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        abort_unless($education->candidate_id === $candidate->id, 404);
        $data = $request->validate([
            'institution' => ['required', 'string', 'max:160'],
            'qualification' => ['nullable', 'string', 'max:160'],
            'started_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'ended_year' => ['nullable', 'integer', 'min:1950', 'max:2100', 'gte:started_year'],
        ]);

        $education->update($data);
        $candidate->increment('profile_version');

        return back()->with('success', 'Education updated.');
    }

    public function destroyEducation(Request $request, CandidateEducation $education): RedirectResponse
    {
        $candidate = $request->user()->candidateProfile();
        abort_unless($education->candidate_id === $candidate->id, 404);
        $education->delete();
        $candidate->increment('profile_version');

        return back()->with('success', 'Education removed.');
    }
}

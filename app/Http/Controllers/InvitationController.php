<?php

namespace App\Http\Controllers;

use App\Models\CandidateInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    public function index(Request $request): Response
    {
        $candidate = $request->user()->candidateProfile();
        $candidate->invitations()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired']);

        return Inertia::render('invitations/index', [
            'invitations' => $candidate->invitations()->latest()->get([
                'id', 'employer_name', 'role_title', 'role_summary', 'status', 'expires_at', 'responded_at', 'created_at',
            ]),
        ]);
    }

    public function respond(Request $request, CandidateInvitation $invitation): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', 'in:accepted,declined']]);
        $candidate = $request->user()->candidateProfile();
        abort_unless($invitation->candidate_id === $candidate->id, 404);

        if ($invitation->status !== 'pending' || ($invitation->expires_at && $invitation->expires_at->isPast())) {
            return back()->withErrors(['invitation' => 'This invitation is no longer available.']);
        }

        $invitation->update(['status' => $data['decision'], 'responded_at' => now()]);

        return back()->with('success', $data['decision'] === 'accepted'
            ? 'Invitation accepted. Your contact details have not been shared yet.'
            : 'Invitation declined. Your contact details were not shared.');
    }
}
